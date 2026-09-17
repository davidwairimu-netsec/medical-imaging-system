<?php
/**
 * Backup Controller
 * Handles full backups (DB + images), verification, and restore.
 */
class BackupController
{
    public function index(): void
    {
        $this->requireAdmin();

        $backups = Backup::getAllWithCreator();
        $totalSize = Backup::getTotalSize();
        $success = Session::flash('success');
        $error = Session::flash('error');
        $warning = Session::flash('warning');

        $pageTitle = 'Backup Management';
        require APP_ROOT . '/views/backups/index.php';
    }

    public function create(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        CSRF::verify();

        $result = BackupManager::createFull();

        if ($result['success']) {
            $backupId = Backup::create([
                'file_name'        => $result['filename'],
                'file_path'        => 'backups/' . $result['filename'],
                'file_size'        => $result['size'],
                'created_by'       => Auth::id(),
                'backup_status'    => 'success',
                'notes'            => "Full backup: DB + {$result['images_count']} images in {$result['duration']}s",
            ]);

            // Update the extra fields added in this phase
            $db = Database::getInstance();
            $db->prepare("
                UPDATE backups 
                SET backup_type = 'full',
                    file_checksum = ?,
                    includes_images = 1
                WHERE backup_id = ?
            ")->execute([$result['checksum'], $backupId]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'BACKUP_CREATE_FULL',
                'entity_type' => 'backup',
                'entity_id' => $backupId,
                'description' => "Full backup: {$result['filename']} ({$result['images_count']} images, " . 
                                 BackupManager::humanSize($result['size']) . ")",
            ]);

            Session::flash('success', 
                "Backup created: {$result['filename']} — " . 
                BackupManager::humanSize($result['size']) . 
                " ({$result['images_count']} images)");
        } else {
            Backup::create([
                'file_name'     => 'failed_' . date('Y-m-d_H-i-s') . '.zip',
                'file_path'     => 'backups/',
                'file_size'     => 0,
                'created_by'    => Auth::id(),
                'backup_status' => 'failed',
                'notes'         => $result['message'],
            ]);

            Session::flash('error', 'Backup failed: ' . $result['message']);
        }

        header('Location: ' . BASE_URL . '/index.php?page=backups');
        exit;
    }

    public function download(): void
    {
        $this->requireAdmin();

        $id = (int) ($_GET['id'] ?? 0);
        $backup = Backup::find($id);

        if (!$backup || $backup['backup_status'] !== 'success') {
            http_response_code(404);
            exit;
        }

        $filepath = APP_ROOT . '/backups/' . $backup['file_name'];

        if (!file_exists($filepath)) {
            Session::flash('error', 'Backup file is missing from disk.');
            header('Location: ' . BASE_URL . '/index.php?page=backups');
            exit;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'BACKUP_DOWNLOAD',
            'entity_type' => 'backup',
            'entity_id' => $id,
            'description' => "Downloaded backup: {$backup['file_name']}",
        ]);

        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($filepath));
        header('Content-Disposition: attachment; filename="' . $backup['file_name'] . '"');
        readfile($filepath);
        exit;
    }

    public function verify(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        CSRF::verify();

        $id = (int) ($_POST['id'] ?? 0);
        $result = BackupManager::verify($id);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'BACKUP_VERIFY',
            'entity_type' => 'backup',
            'entity_id' => $id,
            'description' => "Backup verification: " . $result['message'],
        ]);

        Session::flash($result['success'] ? 'success' : 'error', 
            'Verification: ' . $result['message']);

        header('Location: ' . BASE_URL . '/index.php?page=backups');
        exit;
    }

    public function restore(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        CSRF::verify();

        $id = (int) ($_POST['id'] ?? 0);
        $restoreDb = !empty($_POST['restore_database']);
        $confirm = $_POST['confirm'] ?? '';

        if ($confirm !== 'RESTORE') {
            Session::flash('error', 'Restore cancelled: confirmation text did not match.');
            header('Location: ' . BASE_URL . '/index.php?page=backups');
            exit;
        }

        $result = BackupManager::restore($id, $restoreDb);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'BACKUP_RESTORE',
            'entity_type' => 'backup',
            'entity_id' => $id,
            'description' => "Restore: " . $result['message'] . 
                             ($restoreDb ? " (including database)" : " (images only)"),
        ]);

        Session::flash($result['success'] ? 'success' : 'error', 
            'Restore: ' . $result['message']);

        header('Location: ' . BASE_URL . '/index.php?page=backups');
        exit;
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
