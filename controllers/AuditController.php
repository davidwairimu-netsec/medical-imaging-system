<?php
/**
 * Audit Log Controller
 */
class AuditController
{
    public function index(): void
    {
        $this->requireAdmin();

        $filters = [
            'action' => $_GET['action'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $logs = AuditLog::getRecent(100, $filters);
        $users = User::getAllWithRoles();
        $stats = AuditLog::getStats();

        require APP_ROOT . '/views/audit/index.php';
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