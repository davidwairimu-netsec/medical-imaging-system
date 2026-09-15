<?php
/**
 * User Management Controller
 */
class UserController
{
    public function index(): void
    {
        $this->requireAdmin();

        $users = User::getAllWithRoles();
        require APP_ROOT . '/views/users/index.php';
    }

    public function create(): void
    {
        $this->requireAdmin();

        $errors = [];
        $old = [];
        $roles = User::getRoles();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $data = [
                'full_name' => trim($_POST['full_name'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'role_id' => (int) ($_POST['role_id'] ?? 0),
                'account_status' => $_POST['account_status'] ?? 'active',
            ];

            $old = $data;

            $errors = $this->validateUser($data);

            if (empty($errors)) {
                // Check for existing username/email
                $existing = User::findByUsername($data['username']);
                if ($existing) {
                    $errors['username'] = 'Username already exists.';
                }

                $stmt = Database::getInstance()->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->execute([$data['email']]);
                if ($stmt->fetch()) {
                    $errors['email'] = 'Email address already exists.';
                }
            }

            if (empty($errors)) {
                $userId = User::createUser($data);

                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => AUDIT_USER_CREATE,
                    'entity_type' => 'user',
                    'entity_id' => $userId,
                    'description' => "Created new user: {$data['username']}",
                ]);

                Session::flash('success', 'User created successfully.');
                header('Location: ' . BASE_URL . '/index.php?page=users');
                exit;
            }
        }

        require APP_ROOT . '/views/users/create.php';
    }

    public function edit(): void
    {
        $this->requireAdmin();

        $id = (int) ($_GET['id'] ?? 0);
        $user = User::find($id);

        if (!$user) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }

        $roles = User::getRoles();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $data = [
                'full_name' => trim($_POST['full_name'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'role_id' => (int) ($_POST['role_id'] ?? 0),
                'account_status' => $_POST['account_status'] ?? 'active',
            ];

            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password'];
            }

            $errors = $this->validateUser($data, $id);

            if (empty($errors)) {
                User::updateUser($id, $data);

                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => AUDIT_USER_UPDATE,
                    'entity_type' => 'user',
                    'entity_id' => $id,
                    'description' => "Updated user: {$data['username']}",
                ]);

                Session::flash('success', 'User updated successfully.');
                header('Location: ' . BASE_URL . '/index.php?page=users');
                exit;
            }

            $user = array_merge($user, $data);
        }

        require APP_ROOT . '/views/users/edit.php';
    }

    public function toggleStatus(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        CSRF::verify();

        $id = (int) ($_POST['id'] ?? 0);
        $user = User::find($id);

        if (!$user) {
            http_response_code(404);
            exit;
        }

        // Prevent disabling self
        if ($id === Auth::id()) {
            Session::flash('error', 'You cannot disable your own account.');
            header('Location: ' . BASE_URL . '/index.php?page=users');
            exit;
        }

        $newStatus = $user['account_status'] === STATUS_ACTIVE ? STATUS_DISABLED : STATUS_ACTIVE;
        User::update($id, ['account_status' => $newStatus]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => AUDIT_USER_DISABLE,
            'entity_type' => 'user',
            'entity_id' => $id,
            'description' => "Changed account status to {$newStatus} for user: {$user['username']}",
        ]);

        Session::flash('success', "User account status changed to {$newStatus}.");
        header('Location: ' . BASE_URL . '/index.php?page=users');
        exit;
    }

    public function resetPassword(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        CSRF::verify();

        $id = (int) ($_POST['id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';

        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            Session::flash('error', 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.');
            header('Location: ' . BASE_URL . '/index.php?page=users');
            exit;
        }

        $user = User::find($id);
        if (!$user) {
            http_response_code(404);
            exit;
        }

        User::update($id, ['password' => $newPassword]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => AUDIT_USER_UPDATE,
            'entity_type' => 'user',
            'entity_id' => $id,
            'description' => "Reset password for user: {$user['username']}",
        ]);

        Session::flash('success', 'Password reset successfully.');
        header('Location: ' . BASE_URL . '/index.php?page=users');
        exit;
    }

    private function validateUser(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        if (empty($data['full_name'])) {
            $errors['full_name'] = 'Full name is required.';
        }
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $data['username'])) {
            $errors['username'] = 'Username must be 3-50 characters (letters, numbers, underscores).';
        }
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        }
        if (empty($data['role_id'])) {
            $errors['role_id'] = 'Role is required.';
        }

        // Password required on create
        if ($excludeId === null && empty($data['password'])) {
            $errors['password'] = 'Password is required.';
        } elseif (!empty($data['password']) && strlen($data['password']) < PASSWORD_MIN_LENGTH) {
            $errors['password'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
        }

        return $errors;
    }

    private function requireAdmin(): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
        if (!Auth::isRole(ROLE_ADMIN)) {
            http_response_code(403);
            require APP_ROOT . '/views/errors/403.php';
            exit;
        }
    }
}