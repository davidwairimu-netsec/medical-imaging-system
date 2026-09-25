<?php
/**
 * Head Nurse Controller
 * Manages nurses, assignments, shifts within their department.
 */
class HeadNurseController
{
    private function requirePermission(string $permission): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
        if (!Auth::hasPermission($permission)) {
            http_response_code(403);
            require APP_ROOT . '/views/errors/403.php';
            exit;
        }
    }

    private function db(): PDO { return Database::getInstance(); }

    public function index(): void
    {
        $this->requirePermission('manage_nurses');
        $db = $this->db();

        $stats = [
            'total_nurses' => (int) $db->query("SELECT COUNT(*) FROM nurses WHERE record_status='active'")->fetchColumn(),
            'available'    => (int) $db->query("SELECT COUNT(*) FROM nurses WHERE availability_status='available' AND record_status='active'")->fetchColumn(),
            'busy'         => (int) $db->query("SELECT COUNT(*) FROM nurses WHERE availability_status='busy' AND record_status='active'")->fetchColumn(),
            'assignments'  => (int) $db->query("SELECT COUNT(*) FROM nurse_patient_assignments WHERE assignment_status='active'")->fetchColumn(),
            'unassigned'   => (int) $db->query("SELECT COUNT(*) FROM patients p WHERE p.record_status='active' AND NOT EXISTS (SELECT 1 FROM nurse_patient_assignments npa WHERE npa.patient_id=p.patient_id AND npa.assignment_status='active')")->fetchColumn(),
            'shifts_today' => (int) $db->query("SELECT COUNT(*) FROM nurse_shifts WHERE shift_date=CURDATE()")->fetchColumn(),
        ];

        $workload = Nurse::getWorkload();

        $todayShifts = $db->query("
            SELECT s.*, n.first_name, n.last_name, n.staff_number
            FROM nurse_shifts s
            INNER JOIN nurses n ON s.nurse_id=n.nurse_id
            WHERE s.shift_date=CURDATE()
            ORDER BY s.start_time
        ")->fetchAll();

        $pageTitle = 'Nursing Dashboard';
        require APP_ROOT . '/views/head_nurse/index.php';
    }

    public function nurses(): void
    {
        $this->requirePermission('manage_nurses');
        $db = $this->db();

        $search       = $_GET['search'] ?? '';
        $departmentId = (int) ($_GET['department'] ?? 0);
        $availability = $_GET['availability'] ?? '';

        $sql = "SELECT n.*, d.department_name, u.username
                FROM nurses n
                INNER JOIN departments d ON n.department_id=d.department_id
                LEFT JOIN users u ON n.user_id = u.user_id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (n.first_name LIKE ? OR n.last_name LIKE ? OR n.staff_number LIKE ?)";
            $t = "%{$search}%";
            $params = array_merge($params, [$t, $t, $t]);
        }
        if ($departmentId) { $sql .= " AND n.department_id=?"; $params[] = $departmentId; }
        if ($availability) { $sql .= " AND n.availability_status=?"; $params[] = $availability; }

        $sql .= " ORDER BY n.first_name, n.last_name";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $nurses = $stmt->fetchAll();

        $departments = Nurse::getDepartments();

        $pageTitle = 'Manage Nurses';
        require APP_ROOT . '/views/head_nurse/nurses.php';
    }

    public function createNurse(): void
    {
        $this->requirePermission('manage_nurses');
        $db = $this->db();

        $errors = [];
        $old = [];
        $departments = Nurse::getDepartments();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();
            $data = [
                'staff_number'        => trim($_POST['staff_number'] ?? ''),
                'first_name'          => trim($_POST['first_name'] ?? ''),
                'last_name'           => trim($_POST['last_name'] ?? ''),
                'email'               => trim($_POST['email'] ?? ''),
                'phone'               => trim($_POST['phone'] ?? ''),
                'department_id'       => (int) ($_POST['department_id'] ?? 0),
                'nurse_rank'          => $_POST['nurse_rank'] ?? 'Registered',
                'availability_status' => $_POST['availability_status'] ?? 'available',
                'max_patient_load'    => (int) ($_POST['max_patient_load'] ?? 6),
            ];
            $old = $data;

            if (!$data['staff_number']) $errors['staff_number'] = 'Staff number is required.';
            if (!$data['first_name'])   $errors['first_name']   = 'First name is required.';
            if (!$data['last_name'])    $errors['last_name']    = 'Last name is required.';
            if (!$data['department_id']) $errors['department_id'] = 'Department is required.';

            if (empty($errors)) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM nurses WHERE staff_number=?");
                $stmt->execute([$data['staff_number']]);
                if ($stmt->fetchColumn() > 0) {
                    $errors['staff_number'] = 'Staff number already exists.';
                }
            }

            if (empty($errors)) {
                $stmt = $db->prepare("
                    INSERT INTO nurses (staff_number, first_name, last_name, email, phone,
                                        department_id, nurse_rank, availability_status, max_patient_load)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $data['staff_number'], $data['first_name'], $data['last_name'],
                    $data['email'] ?: null, $data['phone'] ?: null,
                    $data['department_id'], $data['nurse_rank'],
                    $data['availability_status'], $data['max_patient_load']
                ]);
                $nurseId = (int) $db->lastInsertId();

                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'NURSE_CREATE',
                    'entity_type' => 'nurse',
                    'entity_id' => $nurseId,
                    'description' => "Created nurse: {$data['first_name']} {$data['last_name']} ({$data['staff_number']})",
                ]);

                Session::flash('success', 'Nurse added successfully.');
                header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=nurses');
                exit;
            }
        }

        $pageTitle = 'Add Nurse';
        require APP_ROOT . '/views/head_nurse/create_nurse.php';
    }

    public function editNurse(): void
    {
        $this->requirePermission('manage_nurses');
        $db = $this->db();

        $id = (int) ($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM nurses WHERE nurse_id=?");
        $stmt->execute([$id]);
        $nurse = $stmt->fetch();

        if (!$nurse) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }

        $departments = Nurse::getDepartments();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();
            $data = [
                'first_name'          => trim($_POST['first_name'] ?? ''),
                'last_name'           => trim($_POST['last_name'] ?? ''),
                'email'               => trim($_POST['email'] ?? ''),
                'phone'               => trim($_POST['phone'] ?? ''),
                'department_id'       => (int) ($_POST['department_id'] ?? 0),
                'nurse_rank'          => $_POST['nurse_rank'] ?? 'Registered',
                'availability_status' => $_POST['availability_status'] ?? 'available',
                'max_patient_load'    => (int) ($_POST['max_patient_load'] ?? 6),
                'record_status'       => $_POST['record_status'] ?? 'active',
            ];

            if (empty($errors)) {
                $stmt = $db->prepare("
                    UPDATE nurses SET first_name=?, last_name=?, email=?, phone=?,
                        department_id=?, nurse_rank=?, availability_status=?,
                        max_patient_load=?, record_status=?
                    WHERE nurse_id=?
                ");
                $stmt->execute([
                    $data['first_name'], $data['last_name'],
                    $data['email'] ?: null, $data['phone'] ?: null,
                    $data['department_id'], $data['nurse_rank'],
                    $data['availability_status'], $data['max_patient_load'],
                    $data['record_status'], $id
                ]);

                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'NURSE_UPDATE',
                    'entity_type' => 'nurse',
                    'entity_id' => $id,
                    'description' => "Updated nurse: {$data['first_name']} {$data['last_name']}",
                ]);

                Session::flash('success', 'Nurse updated successfully.');
                header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=nurses');
                exit;
            }
            $nurse = array_merge($nurse, $data);
        }

        $pageTitle = 'Edit Nurse';
        require APP_ROOT . '/views/head_nurse/edit_nurse.php';
    }

    public function assignments(): void
    {
        $this->requirePermission('assign_nurse_patient');
        $db = $this->db();

        $npAssignments = $db->query("
            SELECT npa.*, n.first_name AS nurse_first, n.last_name AS nurse_last, n.staff_number,
                   p.first_name AS patient_first, p.last_name AS patient_last, p.hospital_number,
                   u.full_name AS assigned_by_name
            FROM nurse_patient_assignments npa
            INNER JOIN nurses n ON npa.nurse_id=n.nurse_id
            INNER JOIN patients p ON npa.patient_id=p.patient_id
            LEFT JOIN users u ON npa.assigned_by=u.user_id
            WHERE npa.assignment_status='active'
            ORDER BY npa.assigned_at DESC
        ")->fetchAll();

        $ndAssignments = $db->query("
            SELECT nda.*, n.first_name AS nurse_first, n.last_name AS nurse_last, n.staff_number,
                   u.full_name AS doctor_name, a.full_name AS assigned_by_name
            FROM nurse_doctor_assignments nda
            INNER JOIN nurses n ON nda.nurse_id=n.nurse_id
            INNER JOIN users u ON nda.doctor_id=u.user_id
            LEFT JOIN users a ON nda.assigned_by=a.user_id
            WHERE nda.assignment_status='active'
            ORDER BY nda.assigned_at DESC
        ")->fetchAll();

        $nurses   = $db->query("SELECT nurse_id, first_name, last_name, staff_number FROM nurses WHERE record_status='active' ORDER BY first_name")->fetchAll();
        $patients = $db->query("SELECT patient_id, first_name, last_name, hospital_number FROM patients WHERE record_status='active' ORDER BY first_name")->fetchAll();
        $doctors  = $db->query("SELECT u.user_id, u.full_name FROM users u INNER JOIN roles r ON u.role_id=r.role_id WHERE r.role_name='doctor' AND u.account_status='active' ORDER BY u.full_name")->fetchAll();

        $pageTitle = 'Nurse Assignments';
        require APP_ROOT . '/views/head_nurse/assignments.php';
    }

    public function assignNursePatient(): void
    {
        $this->requirePermission('assign_nurse_patient');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        CSRF::verify();

        $nurseId   = (int) ($_POST['nurse_id'] ?? 0);
        $patientId = (int) ($_POST['patient_id'] ?? 0);
        $priority  = $_POST['priority'] ?? 'normal';
        $notes     = trim($_POST['notes'] ?? '');

        if (!$nurseId || !$patientId) {
            Session::flash('error', 'Nurse and patient are required.');
            header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=assignments');
            exit;
        }

        $db = $this->db();
        $db->prepare("UPDATE nurse_patient_assignments SET assignment_status='ended', ended_at=NOW()
                     WHERE patient_id=? AND assignment_status='active'")->execute([$patientId]);

        $db->prepare("INSERT INTO nurse_patient_assignments (nurse_id, patient_id, assigned_by, priority, notes)
                     VALUES (?, ?, ?, ?, ?)")
           ->execute([$nurseId, $patientId, Auth::id(), $priority, $notes ?: null]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'NURSE_PATIENT_ASSIGN',
            'entity_type' => 'nurse_patient_assignment',
            'entity_id' => (int) $db->lastInsertId(),
            'description' => "Assigned patient #{$patientId} to nurse #{$nurseId}",
        ]);

        Session::flash('success', 'Nurse assigned to patient.');
        header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=assignments');
        exit;
    }

    public function assignNurseDoctor(): void
    {
        $this->requirePermission('assign_nurse_doctor');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        CSRF::verify();

        $nurseId  = (int) ($_POST['nurse_id'] ?? 0);
        $doctorId = (int) ($_POST['doctor_id'] ?? 0);
        $notes    = trim($_POST['notes'] ?? '');

        if (!$nurseId || !$doctorId) {
            Session::flash('error', 'Nurse and doctor are required.');
            header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=assignments');
            exit;
        }

        $db = $this->db();
        $db->prepare("INSERT INTO nurse_doctor_assignments (nurse_id, doctor_id, assigned_by, notes)
                     VALUES (?, ?, ?, ?)")->execute([$nurseId, $doctorId, Auth::id(), $notes ?: null]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'NURSE_DOCTOR_ASSIGN',
            'entity_type' => 'nurse_doctor_assignment',
            'entity_id' => (int) $db->lastInsertId(),
            'description' => "Assigned nurse #{$nurseId} to doctor #{$doctorId}",
        ]);

        Session::flash('success', 'Nurse assigned to doctor.');
        header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=assignments');
        exit;
    }

    public function endAssignment(): void
    {
        $this->requirePermission('assign_nurse_patient');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        CSRF::verify();

        $id   = (int) ($_POST['id'] ?? 0);
        $type = $_POST['type'] ?? 'patient';
        $table = ($type === 'doctor') ? 'nurse_doctor_assignments' : 'nurse_patient_assignments';

        $db = $this->db();
        $db->prepare("UPDATE {$table} SET assignment_status='ended', ended_at=NOW() WHERE assignment_id=?")->execute([$id]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'NURSE_ASSIGNMENT_END',
            'entity_type' => $table,
            'entity_id' => $id,
            'description' => "Ended assignment #{$id} ({$type})",
        ]);

        Session::flash('success', 'Assignment ended.');
        header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=assignments');
        exit;
    }

    public function workload(): void
    {
        $this->requirePermission('view_nursing_reports');
        $db = $this->db();

        $workload = Nurse::getWorkload();

        $unassigned = $db->query("
            SELECT p.* FROM patients p
            WHERE p.record_status='active' AND NOT EXISTS (
                SELECT 1 FROM nurse_patient_assignments npa
                WHERE npa.patient_id=p.patient_id AND npa.assignment_status='active')
            ORDER BY p.created_at DESC
        ")->fetchAll();

        $pageTitle = 'Nurse Workload';
        require APP_ROOT . '/views/head_nurse/workload.php';
    }

    public function shifts(): void
    {
        $this->requirePermission('manage_shifts');
        $db = $this->db();

        $nurses = $db->query("SELECT nurse_id, first_name, last_name, staff_number FROM nurses WHERE record_status='active' ORDER BY first_name")->fetchAll();

        $shifts = $db->query("
            SELECT s.*, n.first_name, n.last_name, n.staff_number
            FROM nurse_shifts s
            INNER JOIN nurses n ON s.nurse_id=n.nurse_id
            WHERE s.shift_date >= CURDATE() - INTERVAL 7 DAY
            ORDER BY s.shift_date DESC, s.start_time
            LIMIT 100
        ")->fetchAll();

        $pageTitle = 'Nurse Shifts';
        require APP_ROOT . '/views/head_nurse/shifts.php';
    }

    public function createShift(): void
    {
        $this->requirePermission('manage_shifts');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        CSRF::verify();

        $nurseId   = (int) ($_POST['nurse_id'] ?? 0);
        $shiftDate = $_POST['shift_date'] ?? '';
        $shiftType = $_POST['shift_type'] ?? '';

        if (!$nurseId || !$shiftDate || !$shiftType) {
            Session::flash('error', 'Nurse, date and shift type are required.');
            header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=shifts');
            exit;
        }

        $times = [
            'morning'   => ['07:00:00', '15:00:00'],
            'afternoon' => ['15:00:00', '23:00:00'],
            'night'     => ['23:00:00', '07:00:00'],
        ];
        [$start, $end] = $times[$shiftType] ?? ['08:00:00', '16:00:00'];

        $db = $this->db();
        $db->prepare("INSERT INTO nurse_shifts (nurse_id, shift_date, shift_type, start_time, end_time, created_by)
                     VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$nurseId, $shiftDate, $shiftType, $start, $end, Auth::id()]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'SHIFT_CREATE',
            'entity_type' => 'nurse_shift',
            'entity_id' => (int) $db->lastInsertId(),
            'description' => "Created {$shiftType} shift for nurse #{$nurseId} on {$shiftDate}",
        ]);

        Session::flash('success', 'Shift scheduled.');
        header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=shifts');
        exit;
    }

    public function reports(): void
    {
        $this->requirePermission('view_nursing_reports');
        $db = $this->db();

        $departmentStats = $db->query("
            SELECT d.department_name, COUNT(n.nurse_id) AS nurse_count,
                   SUM(CASE WHEN n.availability_status='available' THEN 1 ELSE 0 END) AS available_count
            FROM departments d
            LEFT JOIN nurses n ON d.department_id=n.department_id AND n.record_status='active'
            GROUP BY d.department_id
            ORDER BY d.department_name
        ")->fetchAll();

        $assignmentStats = $db->query("
            SELECT
                (SELECT COUNT(*) FROM nurse_patient_assignments WHERE assignment_status='active') AS active_np,
                (SELECT COUNT(*) FROM nurse_doctor_assignments WHERE assignment_status='active') AS active_nd,
                (SELECT COUNT(*) FROM nurses WHERE availability_status='available' AND record_status='active') AS available,
                (SELECT COUNT(*) FROM nurses WHERE availability_status='busy' AND record_status='active') AS busy,
                (SELECT COUNT(*) FROM patients WHERE record_status='active') AS total_patients
        ")->fetch();

        $pageTitle = 'Nursing Reports';
        require APP_ROOT . '/views/head_nurse/reports.php';
    }

    /**
     * Create a login account for an existing nurse
     */
    public function createNurseLogin(): void
    {
        $this->requirePermission('manage_nurses');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        CSRF::verify();

        $nurseId = (int) ($_POST['nurse_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? 'ChangeMe123!';

        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM nurses WHERE nurse_id = ?");
        $stmt->execute([$nurseId]);
        $nurse = $stmt->fetch();

        if (!$nurse) {
            Session::flash('error', 'Nurse record not found.');
            header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=nurses');
            exit;
        }

        if (!empty($nurse['user_id'])) {
            Session::flash('warning', 'This nurse already has a login account.');
            header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=nurses');
            exit;
        }

        if (empty($username)) {
            $username = strtolower($nurse['first_name'] . '.' . $nurse['last_name']);
        }

        // Validate username format
        if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $username)) {
            Session::flash('error', 'Username must be 3-50 characters (letters, numbers, dots, underscores, dashes).');
            header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=nurses');
            exit;
        }

        // Check username not taken
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            Session::flash('error', "Username '{$username}' already exists.");
            header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=nurses');
            exit;
        }

        // Email
        $email = $nurse['email'] ?: strtolower($username) . '@hospital.demo';
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $email = strtolower($username) . '.' . time() . '@hospital.demo';
        }

        try {
            $db->beginTransaction();

            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

            $stmt = $db->prepare("
                INSERT INTO users (full_name, username, email, phone, password_hash, role_id, account_status)
                VALUES (?, ?, ?, ?, ?, 7, 'active')
            ");
            $stmt->execute([
                $nurse['first_name'] . ' ' . $nurse['last_name'],
                $username,
                $email,
                $nurse['phone'],
                $hash
            ]);
            $newUserId = (int) $db->lastInsertId();

            $stmt = $db->prepare("UPDATE nurses SET user_id = ? WHERE nurse_id = ?");
            $stmt->execute([$newUserId, $nurseId]);

            $db->commit();

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'NURSE_LOGIN_CREATE',
                'entity_type' => 'nurse',
                'entity_id' => $nurseId,
                'description' => "Created login for {$nurse['first_name']} {$nurse['last_name']} (username: {$username})",
            ]);

            Session::flash('success', "Login created for {$nurse['first_name']} {$nurse['last_name']}. Username: {$username}");

        } catch (Exception $e) {
            $db->rollBack();
            error_log('Create nurse login failed: ' . $e->getMessage());
            Session::flash('error', 'Failed to create login: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=nurses');
        exit;
    }

    /**
     * Reset a nurse's password
     */
    public function resetNursePassword(): void
    {
        $this->requirePermission('manage_nurses');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        CSRF::verify();

        $nurseId = (int) ($_POST['nurse_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? 'ChangeMe123!';

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT user_id, first_name, last_name FROM nurses WHERE nurse_id = ?");
        $stmt->execute([$nurseId]);
        $nurse = $stmt->fetch();

        if (!$nurse || !$nurse['user_id']) {
            Session::flash('error', 'Nurse has no user account.');
            header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=nurses');
            exit;
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        $db->prepare("UPDATE users SET password_hash = ?, failed_login_attempts = 0, account_status = 'active' WHERE user_id = ?")
           ->execute([$hash, $nurse['user_id']]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'NURSE_PASSWORD_RESET',
            'entity_type' => 'nurse',
            'entity_id' => $nurseId,
            'description' => "Reset password for {$nurse['first_name']} {$nurse['last_name']}",
        ]);

        Session::flash('success', "Password reset for {$nurse['first_name']} {$nurse['last_name']}.");
        header('Location: ' . BASE_URL . '/index.php?page=head_nurse&action=nurses');
        exit;
    }
}
