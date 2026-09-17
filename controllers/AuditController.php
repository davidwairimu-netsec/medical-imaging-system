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

    public function integrity(): void
    {
        $this->requireAdmin();

        $report = StorageIntegrity::auditAll();
        $stats = StorageIntegrity::getStats();

        $pageTitle = 'Storage Integrity Audit';
        require APP_ROOT . '/views/audit/integrity.php';
    }

    public function fixIntegrity(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        CSRF::verify();

        $action = $_POST['action'] ?? '';
        $imageId = (int) ($_POST['image_id'] ?? 0);

        if ($action === 'archive_broken' && $imageId) {
            $image = MedicalImage::find($imageId);
            if ($image) {
                $integrity = StorageIntegrity::checkImage($image);
                if ($integrity['status'] !== StorageIntegrity::STATUS_OK
                    && $integrity['status'] !== StorageIntegrity::STATUS_SIZE_MISMATCH) {
                    MedicalImage::update($imageId, ['record_status' => 'archived']);
                    AuditLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'IMAGE_INTEGRITY_ARCHIVE',
                        'entity_type' => 'medical_image',
                        'entity_id' => $imageId,
                        'description' => "Auto-archived broken image #{$imageId}: {$integrity['message']}",
                    ]);
                    Session::flash('success', "Image #{$imageId} archived.");
                } else {
                    Session::flash('warning', "Image #{$imageId} is fine.");
                }
            }
        }

        header('Location: ' . BASE_URL . '/index.php?page=audit&action=integrity');
        exit;
    }
}
