<?php
/**
 * Backup Management Controller
 */
class BackupController
{
    public function index(): void
    {
        $this->requireAdmin();

        $backups = Backup::getAllWithCreator();
        $message = Session::flash('success');
        $error = Session::flash('error');

        require APP_ROOT . '/views/backups/index.php';
    }

    public function create(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        CSRF::verify();

        $config = require APP_ROOT . '/config/database.php';

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = BACKUP_DIR . $filename;

        if (!is_dir(BACKUP_DIR)) {
            mkdir(BACKUP_DIR, 0755, true);
        }

        // Build mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --port=%d --user=%s %s %s > %s 2>&1',
            escapeshellarg($config['host']),
            (int) $config['port'],
            escapeshellarg($config['username']),
            !empty($config['password']) ? '--password=' . escapeshellarg($config['password']) : '',
            escapeshellarg($config['database']),
            escapeshellarg($filepath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($filepath) && filesize($filepath) > 0) {
            $backupId = Backup::create([
                'file_name' => $filename,
                'file_path' => 'backups/' . $filename,
                'file_size' => filesize($filepath),
                'created_by' => Auth::id(),
                'backup_status' => 'success',
                'notes' => 'Manual database backup',
            ]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => AUDIT_BACKUP_CREATE,
                'entity_type' => 'backup',
                'entity_id' => $backupId,
                'description' => "Created database backup: {$filename}",
            ]);

            Session::flash('success', 'Database backup created successfully.');
        } else {
            Backup::create([
                'file_name' => $filename,
                'file_path' => 'backups/' . $filename,
                'file_size' => 0,
                'created_by' => Auth::id(),
                'backup_status' => 'failed',
                'notes' => 'Backup failed. Ensure mysqldump is available in system PATH.',
            ]);

            Session::flash('error', 'Backup failed. Ensure mysqldump is available and accessible.');
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

        $filepath = BACKUP_DIR . $backup['file_name'];

        if (!file_exists($filepath)) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: application/sql');
        header('Content-Length: ' . filesize($filepath));
        header('Content-Disposition: attachment; filename="' . $backup['file_name'] . '"');
        readfile($filepath);
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