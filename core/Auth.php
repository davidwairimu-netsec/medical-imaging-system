<?php
/**
 * Authentication Manager
 */
class Auth
{
    private static ?array $user = null;

    public static function attempt(string $username, string $password): array
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT u.*, r.role_name 
            FROM users u
            INNER JOIN roles r ON u.role_id = r.role_id
            WHERE u.username = :username OR u.email = :email
            LIMIT 1
        ");
        $stmt->execute([':username' => $username, ':email' => $username]);
        $user = $stmt->fetch();

        if (!$user) {
            self::logFailedAttempt($username, 'User not found');
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        // Check account status
        if ($user['account_status'] === STATUS_DISABLED) {
            self::logFailedAttempt($username, 'Account disabled');
            return ['success' => false, 'message' => 'Your account has been disabled. Contact administrator.'];
        }

        if ($user['account_status'] === STATUS_LOCKED) {
            self::logFailedAttempt($username, 'Account locked');
            return ['success' => false, 'message' => 'Your account is locked. Contact administrator.'];
        }

        // Check lockout
        if ($user['failed_login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $db->prepare("UPDATE users SET account_status = 'locked' WHERE user_id = ?")
               ->execute([$user['user_id']]);
            self::logFailedAttempt($username, 'Account locked due to too many failed attempts');
            return ['success' => false, 'message' => 'Account locked due to too many failed attempts.'];
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $attempts = $user['failed_login_attempts'] + 1;
            $status = $attempts >= MAX_LOGIN_ATTEMPTS ? 'locked' : 'active';

            $db->prepare("UPDATE users SET failed_login_attempts = ?, account_status = ? WHERE user_id = ?")
               ->execute([$attempts, $status, $user['user_id']]);

            self::logFailedAttempt($username, 'Invalid password');
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        // Successful login
        $db->prepare("
            UPDATE users 
            SET failed_login_attempts = 0, last_login = NOW(), account_status = 'active' 
            WHERE user_id = ?
        ")->execute([$user['user_id']]);

        // Regenerate session
        Session::regenerate();

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role_name'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['logged_in_at'] = time();

        self::$user = $user;

        // Log successful login
        AuditLog::create([
            'user_id' => $user['user_id'],
            'action' => AUDIT_LOGIN,
            'entity_type' => 'user',
            'entity_id' => $user['user_id'],
            'description' => 'User logged in successfully',
        ]);

        return ['success' => true, 'user' => $user];
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (self::$user === null && self::check()) {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT u.*, r.role_name 
                FROM users u
                INNER JOIN roles r ON u.role_id = r.role_id
                WHERE u.user_id = ?
                LIMIT 1
            ");
            $stmt->execute([$_SESSION['user_id']]);
            self::$user = $stmt->fetch() ?: null;
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    public static function isRole(string ...$roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    public static function hasPermission(string $permission): bool
    {
        $permissions = [
            ROLE_ADMIN => [
                'manage_users', 'view_audit', 'perform_backup',
                'delete_image', 'request_delete_image', 'approve_deletion',
                'register_patient', 'edit_patient', 'view_patient',
                'upload_image', 'view_image', 'archive_image', 'search_records'
            ],
            ROLE_HEAD_NURSE => [
                'manage_nurses', 'assign_nurse_doctor', 'assign_nurse_patient',
                'manage_shifts', 'view_nursing_reports', 'view_department_audit',
                'view_patient', 'view_image', 'search_records'
            ],
            ROLE_RADIOLOGIST => [
                'view_patient', 'upload_image', 'view_image',
                'archive_image', 'search_records'
            ],
            ROLE_TECHNICIAN => [
                'register_patient', 'view_patient', 'upload_image',
                'view_image', 'search_records', 'request_delete_image'
            ],
            ROLE_DOCTOR => [
                'view_patient', 'view_image', 'search_records'
            ],
            ROLE_NURSE => [
                'nurse_portal',
                'view_assigned_patients',
                'view_medications',
            ],
            ROLE_RECORDS => [
                'register_patient', 'edit_patient', 'view_patient',
                'view_image', 'search_records'
            ],
        ];

        $role = self::role();
        return in_array($permission, $permissions[$role] ?? [], true);
    }

    public static function logout(): void
    {
        if (self::check()) {
            AuditLog::create([
                'user_id' => self::id(),
                'action' => AUDIT_LOGOUT,
                'entity_type' => 'user',
                'entity_id' => self::id(),
                'description' => 'User logged out',
            ]);
        }

        Session::destroy();
    }

    private static function logFailedAttempt(string $username, string $reason): void
    {
        try {
            AuditLog::create([
                'user_id' => null,
                'action' => AUDIT_LOGIN_FAILED,
                'entity_type' => 'user',
                'entity_id' => null,
                'description' => "Failed login attempt for username '{$username}': {$reason}",
            ]);
        } catch (Exception $e) {
            error_log('Failed to log login attempt: ' . $e->getMessage());
        }
    }
}