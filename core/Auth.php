<?php
/**
 * Authentication Manager
 */
class Auth
{
    private static ?array $user = null;

    /**
     * Attempt to authenticate a user.
     *
     * Returns:
     *   ['status' => 'success'|'invalid_username'|'invalid_password'|'rate_limited', 'user' => array|null]
     */
    /**
     * Attempt to authenticate a user.
     *
     * Returns:
     *   ['status' => 'success'|'invalid_username'|'invalid_password'|'rate_limited', 'user' => array|null]
     */
    public static function attempt(string $username, string $password): array
    {
        $db = Database::getInstance();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Rate limit: max 5 failures in last 15 minutes (per username OR IP)
        $maxAttempts = 5;
        $lockoutMinutes = 15;

        $sql = "SELECT COUNT(*) AS cnt
                FROM login_attempts
                WHERE (username = :username OR ip_address = :ip)
                  AND success = 0
                  AND attempt_time > (NOW() - INTERVAL " . (int)$lockoutMinutes . " MINUTE)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':username' => $username, ':ip' => $ip]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ((int) $row['cnt'] >= $maxAttempts) {
            self::recordAttempt($username, $ip, false);
            self::logFailedAttempt($username, 'rate_limited');

            // Explicit forensic audit log for blocked attempts
            AuditLog::create([
                'user_id' => null,
                'action' => 'USER_LOGIN_BLOCKED',
                'entity_type' => 'user',
                'entity_id' => null,
                'description' => "Login blocked (rate limit): {$username}",
                'ip_address' => $ip,
            ]);

            return ['status' => 'rate_limited', 'user' => null];
        }

        // Look up user (by username OR email)
        $user = User::findByUsernameOrEmail($username);

        if (!$user) {
            self::recordAttempt($username, $ip, false);
            self::logFailedAttempt($username, 'unknown_username');
            return ['status' => 'invalid_username', 'user' => null];
        }

        // Check account status — return same as invalid_username to prevent enumeration
        if (($user['account_status'] ?? 'active') !== 'active') {
            self::recordAttempt($username, $ip, false);
            self::logFailedAttempt($username, 'account_' . ($user['account_status'] ?? 'unknown'));
            return ['status' => 'invalid_username', 'user' => null];
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            self::recordAttempt($username, $ip, false);

            $upd = $db->prepare("UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE user_id = :user_id");
            $upd->execute([':user_id' => $user['user_id']]);

            self::logFailedAttempt($username, 'wrong_password');
            return ['status' => 'invalid_password', 'user' => $user];
        }

        // Success — reset counters
        self::recordAttempt($username, $ip, true);

        $reset = $db->prepare("UPDATE users SET failed_login_attempts = 0, last_login = NOW() WHERE user_id = :user_id");
        $reset->execute([':user_id' => $user['user_id']]);

        // Preserve CSRF token across session regeneration
        if (session_status() === PHP_SESSION_ACTIVE) {
            $csrfToken = $_SESSION['_csrf_token'] ?? null;
            $csrfTime  = $_SESSION['_csrf_token_time'] ?? null;
            session_regenerate_id(true);
            if ($csrfToken !== null) {
                $_SESSION['_csrf_token'] = $csrfToken;
                $_SESSION['_csrf_token_time'] = $csrfTime;
            }
        }

        $_SESSION['user_id']   = (int) $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role_name'] ?? null;
        $_SESSION['role_id']   = (int) $user['role_id'];
        $_SESSION['last_activity'] = time();

        // Log successful login
        AuditLog::create([
            'user_id' => $user['user_id'],
            'action' => 'USER_LOGIN',
            'entity_type' => 'user',
            'entity_id' => $user['user_id'],
            'description' => 'User logged in successfully',
        ]);

        return ['status' => 'success', 'user' => $user];
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


    /**
     * Record a login attempt for rate limiting.
     */
    private static function recordAttempt(string $username, string $ip, bool $success): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO login_attempts (username, ip_address, success) VALUES (:u, :ip, :s)"
        );
        $stmt->execute([
            ':u'  => $username,
            ':ip' => $ip,
            ':s'  => $success ? 1 : 0,
        ]);
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
