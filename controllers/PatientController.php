<?php
/**
 * Patient Management Controller
 */
class PatientController
{
    public function index(): void
    {
        $this->requirePermission('view_patient');

        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $gender = $_GET['gender'] ?? '';
        $page = max(1, (int) ($_GET['page_num'] ?? 1));

        $filters = [
            'search' => $search,
            'status' => $status,
            'gender' => $gender,
            'limit' => RECORDS_PER_PAGE,
            'offset' => ($page - 1) * RECORDS_PER_PAGE,
        ];

        $patients = Patient::search($filters);

        // Count total for pagination
        $countFilters = $filters;
        unset($countFilters['limit'], $countFilters['offset']);
        $total = $this->countPatients($countFilters);
        $totalPages = ceil($total / RECORDS_PER_PAGE);

        require APP_ROOT . '/views/patients/index.php';
    }

    public function create(): void
    {
        $this->requirePermission('register_patient');

        $errors = [];
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $data = [
                'hospital_number' => trim($_POST['hospital_number'] ?? ''),
                'first_name' => trim($_POST['first_name'] ?? ''),
                'middle_name' => trim($_POST['middle_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'date_of_birth' => $_POST['date_of_birth'] ?? '',
                'gender' => $_POST['gender'] ?? '',
                'national_id' => trim($_POST['national_id'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'emergency_contact' => trim($_POST['emergency_contact'] ?? ''),
                'registered_by' => Auth::id(),
            ];

            $old = $data;

            // Validation
            $errors = $this->validatePatient($data);

            // Check duplicates
            if (empty($errors)) {
                $duplicates = Patient::checkDuplicate(
                    $data['hospital_number'],
                    $data['national_id'],
                    $data['phone'],
                    $data['first_name'],
                    $data['last_name'],
                    $data['date_of_birth']
                );

                if (!empty($duplicates)) {
                    $hasExact = false;
                    $messages = [];
                    foreach ($duplicates as $key => $match) {
                        $messages[] = $match['message'];
                        if ($match['confidence'] === 'exact') {
                            $hasExact = true;
                        }
                    }
                    $errors['duplicate'] = implode('; ', $messages);
                    $errors['duplicate_severity'] = $hasExact ? 'exact' : 'possible';
                    $errors['duplicate_records'] = $duplicates;
                }
            }

            if (empty($errors)) {
                $patientId = Patient::create($data);

                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => AUDIT_PATIENT_CREATE,
                    'entity_type' => 'patient',
                    'entity_id' => $patientId,
                    'description' => "Registered new patient: {$data['first_name']} {$data['last_name']} ({$data['hospital_number']})",
                ]);

                Session::flash('success', 'Patient registered successfully.');
                header('Location: ' . BASE_URL . '/index.php?page=patients&action=view&id=' . $patientId);
                exit;
            }
        }

        require APP_ROOT . '/views/patients/create.php';
    }

    public function view(): void
    {
        $this->requirePermission('view_patient');

        $id = (int) ($_GET['id'] ?? 0);
        $patient = Patient::getWithImages($id);

        if (!$patient) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }

        require APP_ROOT . '/views/patients/view.php';
    }

    public function history(): void
    {
        $this->requirePermission('view_patient');

        $id = (int) ($_GET['id'] ?? 0);
        $patient = Patient::getWithImages($id);

        if (!$patient) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }

        require APP_ROOT . '/views/patients/history.php';
    }

    public function edit(): void
    {
        $this->requirePermission('edit_patient');

        $id = (int) ($_GET['id'] ?? 0);
        $patient = Patient::find($id);

        if (!$patient) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $data = [
                'hospital_number' => trim($_POST['hospital_number'] ?? ''),
                'first_name' => trim($_POST['first_name'] ?? ''),
                'middle_name' => trim($_POST['middle_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'date_of_birth' => $_POST['date_of_birth'] ?? '',
                'gender' => $_POST['gender'] ?? '',
                'national_id' => trim($_POST['national_id'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'emergency_contact' => trim($_POST['emergency_contact'] ?? ''),
            ];

            $errors = $this->validatePatient($data, $id);

            if (empty($errors)) {
                Patient::update($id, $data);

                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => AUDIT_PATIENT_UPDATE,
                    'entity_type' => 'patient',
                    'entity_id' => $id,
                    'description' => "Updated patient record: {$data['first_name']} {$data['last_name']}",
                ]);

                Session::flash('success', 'Patient record updated successfully.');
                header('Location: ' . BASE_URL . '/index.php?page=patients&action=view&id=' . $id);
                exit;
            }

            $patient = array_merge($patient, $data);
        }

        require APP_ROOT . '/views/patients/edit.php';
    }

    public function archive(): void
    {
        $this->requirePermission('edit_patient');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        CSRF::verify();

        $id = (int) ($_POST['id'] ?? 0);
        $patient = Patient::find($id);

        if (!$patient) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }

        Patient::update($id, ['record_status' => RECORD_ARCHIVED]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => AUDIT_PATIENT_ARCHIVE,
            'entity_type' => 'patient',
            'entity_id' => $id,
            'description' => "Archived patient record: {$patient['first_name']} {$patient['last_name']}",
        ]);

        Session::flash('success', 'Patient record archived successfully.');
        header('Location: ' . BASE_URL . '/index.php?page=patients');
        exit;
    }

    private function validatePatient(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        if (empty($data['hospital_number'])) {
            $errors['hospital_number'] = 'Hospital number is required.';
        }
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required.';
        }
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required.';
        }
        if (empty($data['date_of_birth'])) {
            $errors['date_of_birth'] = 'Date of birth is required.';
        } elseif (strtotime($data['date_of_birth']) > time()) {
            $errors['date_of_birth'] = 'Date of birth cannot be in the future.';
        }
        if (empty($data['gender'])) {
            $errors['gender'] = 'Gender is required.';
        }

        return $errors;
    }

    private function countPatients(array $filters): int
    {
        $sql = "SELECT COUNT(*) FROM patients p WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.hospital_number LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? 
                          OR p.national_id LIKE ? OR p.phone LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$term, $term, $term, $term, $term]);
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.record_status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['gender'])) {
            $sql .= " AND p.gender = ?";
            $params[] = $filters['gender'];
        }

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function requirePermission(string $permission): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        // NURSE_MUST_USE_NURSE_PORTAL — nurses use ?page=nurse for patient access
        if (Auth::isRole(ROLE_NURSE)) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'NURSE_BLOCKED_PATIENT_CONTROLLER',
                'entity_type' => 'security',
                'entity_id' => null,
                'description' => 'Nurse attempted to access general PatientController — redirected to nurse portal',
            ]);
            Session::flash('error', 'Nurses cannot access the general patient list. Use "My Patients" instead.');
            header('Location: ' . BASE_URL . '/index.php?page=nurse');
            exit;
        }

        if (!Auth::hasPermission($permission)) {
            http_response_code(403);
            require APP_ROOT . '/views/errors/403.php';
            exit;
        }
    }
}
