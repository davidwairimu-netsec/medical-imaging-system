<?php
/**
 * Nurse Portal Controller
 * 
 * Read-only access to ASSIGNED patients only.
 * Nurses cannot add, edit, or delete any records.
 * Nurses cannot view patients who are not assigned to them.
 */
class NurseController
{
    private function requireNurse(): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
        if (!Auth::hasPermission('nurse_portal') && !Auth::isRole(ROLE_ADMIN, ROLE_HEAD_NURSE)) {
            http_response_code(403);
            require APP_ROOT . '/views/errors/403.php';
            exit;
        }
    }

    private function db(): PDO { return Database::getInstance(); }

    /**
     * Get the nurse record ID for the currently logged-in user
     */
    private function getCurrentNurseId(): ?int
    {
        $db = $this->db();
        $stmt = $db->prepare("SELECT nurse_id FROM nurses WHERE user_id = ? AND record_status = 'active' LIMIT 1");
        $stmt->execute([Auth::id()]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    /**
     * Nurse dashboard: assigned patients overview
     */
    public function index(): void
    {
        $this->requireNurse();
        $db = $this->db();

        $nurseId = $this->getCurrentNurseId();
        if (!$nurseId) {
            // Admin or head nurse without a nurse record — show overall view
            $assignments = $db->query("
                SELECT npa.*, 
                       n.first_name AS nurse_first, n.last_name AS nurse_last,
                       n.staff_number, n.availability_status,
                       p.first_name AS patient_first, p.last_name AS patient_last,
                       p.hospital_number, p.date_of_birth, p.gender
                FROM nurse_patient_assignments npa
                INNER JOIN nurses n ON npa.nurse_id = n.nurse_id
                INNER JOIN patients p ON npa.patient_id = p.patient_id
                WHERE npa.assignment_status = 'active'
                ORDER BY npa.assigned_at DESC
            ")->fetchAll();

            $nurse = null;
            $pageTitle = 'Nurse Portal (Overview)';
            require APP_ROOT . '/views/nurse/index.php';
            return;
        }

        // Get this nurse's info
        $stmt = $db->prepare("
            SELECT n.*, d.department_name
            FROM nurses n
            INNER JOIN departments d ON n.department_id = d.department_id
            WHERE n.nurse_id = ?
        ");
        $stmt->execute([$nurseId]);
        $nurse = $stmt->fetch();

        // Get ONLY this nurse's active assignments
        $stmt = $db->prepare("
            SELECT npa.*, 
                   p.patient_id, p.first_name, p.last_name, p.hospital_number,
                   p.date_of_birth, p.gender, p.phone
            FROM nurse_patient_assignments npa
            INNER JOIN patients p ON npa.patient_id = p.patient_id
            WHERE npa.nurse_id = ?
              AND npa.assignment_status = 'active'
            ORDER BY npa.priority DESC, npa.assigned_at DESC
        ");
        $stmt->execute([$nurseId]);
        $assignments = $stmt->fetchAll();

        $pageTitle = 'My Assigned Patients';
        require APP_ROOT . '/views/nurse/index.php';
    }

    /**
     * View an assigned patient's read-only record
     * SECURITY: Only accessible if the patient is actively assigned to this nurse
     */
    public function patient(): void
    {
        $this->requireNurse();
        $db = $this->db();

        $nurseId = $this->getCurrentNurseId();
        $patientId = (int) ($_GET['id'] ?? 0);

        if (!$patientId) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }

        // SECURITY: Verify patient is assigned to this nurse (unless admin/head nurse)
        if ($nurseId && !Auth::isRole(ROLE_ADMIN, ROLE_HEAD_NURSE)) {
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM nurse_patient_assignments
                WHERE nurse_id = ? AND patient_id = ? AND assignment_status = 'active'
            ");
            $stmt->execute([$nurseId, $patientId]);
            if ($stmt->fetchColumn() == 0) {
                // Not assigned — log the attempt and deny
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'NURSE_UNAUTHORIZED_PATIENT_ACCESS',
                    'entity_type' => 'patient',
                    'entity_id' => $patientId,
                    'description' => "Nurse attempted to access unassigned patient #{$patientId}",
                ]);
                http_response_code(403);
                require APP_ROOT . '/views/errors/403.php';
                return;
            }
        }

        // Get patient
        $stmt = $db->prepare("SELECT * FROM patients WHERE patient_id = ?");
        $stmt->execute([$patientId]);
        $patient = $stmt->fetch();

        if (!$patient) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }

        // Get medications
        $medications = Medication::getForPatient($patientId);

        // Get recent imaging (read-only)
        $stmt = $db->prepare("
            SELECT image_id, imaging_type, study_date, body_part, clinical_notes
            FROM medical_images
            WHERE patient_id = ? AND record_status = 'active'
            ORDER BY study_date DESC
            LIMIT 10
        ");
        $stmt->execute([$patientId]);
        $images = $stmt->fetchAll();

        // Get assignment details
        $stmt = $db->prepare("
            SELECT npa.*, 
                   n.first_name AS nurse_first, n.last_name AS nurse_last,
                   n.staff_number, n.phone AS nurse_phone
            FROM nurse_patient_assignments npa
            INNER JOIN nurses n ON npa.nurse_id = n.nurse_id
            WHERE npa.patient_id = ? AND npa.assignment_status = 'active'
        ");
        $stmt->execute([$patientId]);
        $currentAssignment = $stmt->fetch();

        // Audit the access
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'NURSE_VIEW_PATIENT',
            'entity_type' => 'patient',
            'entity_id' => $patientId,
            'description' => "Nurse viewed patient #{$patientId} record",
        ]);

        $pageTitle = 'Patient Record';
        require APP_ROOT . '/views/nurse/patient.php';
    }
}
