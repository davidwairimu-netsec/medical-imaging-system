<?php
/**
 * Profile Controller
 * Handles user profile and password management.
 */
class ProfileController
{
    private function requireLogin(): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
    }

    /**
     * Show change password form
     */
    public function changePassword(): void
    {
        $this->requireLogin();
        $errors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            // Validation
            if (empty($current)) {
                $errors['current_password'] = 'Current password is required.';
            }
            if (empty($new)) {
                $errors['new_password'] = 'New password is required.';
            } elseif (strlen($new) < PASSWORD_MIN_LENGTH) {
                $errors['new_password'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
            } elseif (!preg_match('/[A-Z]/', $new) || !preg_match('/[0-9]/', $new)) {
                $errors['new_password'] = 'Password must contain at least one uppercase letter and one number.';
            }
            if ($new !== $confirm) {
                $errors['confirm_password'] = 'Passwords do not match.';
            }
            if ($current === $new) {
                $errors['new_password'] = 'New password must be different from current password.';
            }

            // Verify current password
            if (empty($errors)) {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT password_hash FROM users WHERE user_id = ?");
                $stmt->execute([Auth::id()]);
                $hash = $stmt->fetchColumn();

                if (!password_verify($current, $hash)) {
                    $errors['current_password'] = 'Current password is incorrect.';
                }
            }

            // Update password
            if (empty($errors)) {
                $db = Database::getInstance();
                $newHash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 10]);
                $db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?")
                   ->execute([$newHash, Auth::id()]);

                AuditLog::create([
                    'user_id'     => Auth::id(),
                    'action'      => 'PASSWORD_CHANGE',
                    'entity_type' => 'user',
                    'entity_id'   => Auth::id(),
                    'description' => 'User changed their password',
                ]);

                Session::flash('success', 'Your password has been changed successfully.');
                header('Location: ' . BASE_URL . '/index.php?page=profile&action=changePassword');
                exit;
            }
        }

        $pageTitle = 'Change Password';
        require APP_ROOT . '/views/profile/change_password.php';
    }
}
