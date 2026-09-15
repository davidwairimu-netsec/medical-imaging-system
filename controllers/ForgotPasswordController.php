<?php
/**
 * Forgot Password Controller
 * Handles password reset requests and reset completion.
 */
class ForgotPasswordController
{
    /**
     * Show forgot password form and process request
     */
    public function forgot(): void
    {
        $errors = [];
        $resetLink = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                $errors['email'] = 'Email address is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format.';
            }

            if (empty($errors)) {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT user_id, username, email FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                // Always show the same message (don't reveal if email exists)
                if ($user) {
                    // Invalidate any previous unused tokens for this user
                    $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL")
                       ->execute([$user['user_id']]);

                    // Generate a secure token
                    $token = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $token);
                    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

                    $db->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at, ip_address)
                                 VALUES (?, ?, ?, ?)")
                       ->execute([$user['user_id'], $tokenHash, $expiresAt, $_SERVER['REMOTE_ADDR'] ?? null]);

                    // Build reset link
                    $resetLink = BASE_URL . '/index.php?page=auth&action=reset&token=' . $token;

                    AuditLog::create([
                        'user_id'     => $user['user_id'],
                        'action'      => 'PASSWORD_RESET_REQUEST',
                        'entity_type' => 'user',
                        'entity_id'   => $user['user_id'],
                        'description' => 'Password reset requested for ' . $user['email'],
                    ]);
                }

                // Always show success message (prevent email enumeration)
                $successMessage = 'If that email is registered, a password reset link has been generated below.';
            }
        }

        require APP_ROOT . '/views/auth/forgot_password.php';
    }

    /**
     * Show reset form and process the new password
     */
    public function reset(): void
    {
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        $errors = [];
        $validToken = false;
        $user = null;

        if (empty($token)) {
            $errors['general'] = 'Missing reset token. Please request a new password reset.';
        } else {
            $tokenHash = hash('sha256', $token);
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT pr.*, u.email, u.username
                FROM password_resets pr
                INNER JOIN users u ON pr.user_id = u.user_id
                WHERE pr.token_hash = ?
                  AND pr.used_at IS NULL
                  AND pr.expires_at > NOW()
                LIMIT 1
            ");
            $stmt->execute([$tokenHash]);
            $reset = $stmt->fetch();

            if (!$reset) {
                $errors['general'] = 'This reset link is invalid, expired, or already used. Please request a new one.';
            } else {
                $validToken = true;
                $user = $reset;
            }
        }

        // Handle new password submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
            CSRF::verify();
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (strlen($new) < PASSWORD_MIN_LENGTH) {
                $errors['new_password'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
            } elseif (!preg_match('/[A-Z]/', $new) || !preg_match('/[0-9]/', $new)) {
                $errors['new_password'] = 'Password must contain at least one uppercase letter and one number.';
            }
            if ($new !== $confirm) {
                $errors['confirm_password'] = 'Passwords do not match.';
            }

            if (empty($errors)) {
                $newHash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 10]);

                // Update password
                $db->prepare("UPDATE users SET password_hash = ?, failed_login_attempts = 0, account_status = 'active' WHERE user_id = ?")
                   ->execute([$newHash, $user['user_id']]);

                // Mark token as used
                $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE reset_id = ?")
                   ->execute([$user['reset_id']]);

                AuditLog::create([
                    'user_id'     => $user['user_id'],
                    'action'      => 'PASSWORD_RESET_SUCCESS',
                    'entity_type' => 'user',
                    'entity_id'   => $user['user_id'],
                    'description' => 'Password reset completed for ' . $user['email'],
                ]);

                Session::flash('success', 'Your password has been reset. You can now log in.');
                header('Location: ' . BASE_URL . '/login.php');
                exit;
            }
        }

        require APP_ROOT . '/views/auth/reset_password.php';
    }
}
