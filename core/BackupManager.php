<?php
/**
 * Backup Manager
 * 
 * Creates full backups: database dump + uploaded image files.
 * Provides restore and verification.
 */
class BackupManager
{
    private const BACKUP_DIR = APP_ROOT . '/backups';

    /**
     * Create a full backup (DB + images).
     * Returns array with status, filename, size, checksum, message.
     */
    public static function createFull(): array
    {
        $startTime = microtime(true);
        $timestamp = date('Y-m-d_H-i-s');
        $baseName = "full_backup_{$timestamp}";
        $tempDir = self::BACKUP_DIR . "/tmp_{$baseName}";

        // Ensure directories exist
        if (!is_dir(self::BACKUP_DIR)) {
            @mkdir(self::BACKUP_DIR, 0755, true);
        }
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        // 1. Dump the database
        $sqlFile = $tempDir . '/database.sql';
        $dbResult = self::dumpDatabase($sqlFile);

        if (!$dbResult['success']) {
            self::cleanup($tempDir);
            return [
                'success' => false,
                'message' => 'Database dump failed: ' . $dbResult['message'],
            ];
        }

        // 2. Copy image files
        $imagesDir = APP_ROOT . '/uploads/medical_images';
        $imagesCopied = 0;
        if (is_dir($imagesDir)) {
            $imagesCopied = self::copyDirectory($imagesDir, $tempDir . '/medical_images');
        }

        // 3. Create manifest
        $manifest = [
            'created_at'      => date('Y-m-d H:i:s'),
            'created_by'      => Auth::id(),
            'db_file'         => 'database.sql',
            'db_size'         => filesize($sqlFile),
            'images_dir'      => 'medical_images',
            'images_count'    => $imagesCopied,
            'app_name'        => APP_NAME,
            'app_version'     => APP_VERSION,
        ];
        file_put_contents(
            $tempDir . '/manifest.json',
            json_encode($manifest, JSON_PRETTY_PRINT)
        );

        // 4. Zip it all
        $zipFile = self::BACKUP_DIR . "/{$baseName}.zip";
        $zipResult = self::zipDirectory($tempDir, $zipFile);

        if (!$zipResult['success']) {
            self::cleanup($tempDir);
            return [
                'success' => false,
                'message' => 'Zip creation failed: ' . $zipResult['message'],
            ];
        }

        // 5. Compute checksum
        $checksum = hash_file('sha256', $zipFile);
        $size = filesize($zipFile);
        $duration = round(microtime(true) - $startTime, 2);

        // 6. Cleanup temp
        self::cleanup($tempDir);

        return [
            'success'       => true,
            'filename'      => "{$baseName}.zip",
            'size'          => $size,
            'checksum'      => $checksum,
            'db_size'       => $manifest['db_size'],
            'images_count'  => $imagesCopied,
            'duration'      => $duration,
            'message'       => "Backup created in {$duration}s",
        ];
    }

    /**
     * Restore a backup. Extracts zip and optionally re-imports SQL.
     * (Restore of SQL requires shell mysql client; may be disabled in some environments.)
     */
    public static function restore(int $backupId, bool $restoreDatabase = false): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM backups WHERE backup_id = ?");
        $stmt->execute([$backupId]);
        $backup = $stmt->fetch();

        if (!$backup) {
            return ['success' => false, 'message' => 'Backup not found'];
        }

        $zipPath = self::BACKUP_DIR . '/' . $backup['file_name'];

        if (!file_exists($zipPath)) {
            return ['success' => false, 'message' => 'Backup file is missing from disk'];
        }

        // Verify checksum first
        $actualChecksum = hash_file('sha256', $zipPath);
        if (!empty($backup['file_checksum']) && $actualChecksum !== $backup['file_checksum']) {
            return ['success' => false, 'message' => 'Checksum mismatch — backup file may be corrupt'];
        }

        // Extract to temp dir
        $tempDir = self::BACKUP_DIR . '/restore_tmp_' . time();
        @mkdir($tempDir, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            self::cleanup($tempDir);
            return ['success' => false, 'message' => 'Cannot open backup zip'];
        }
        $zip->extractTo($tempDir);
        $zip->close();

        // Restore images
        $imagesRestored = 0;
        $imagesSrc = $tempDir . '/medical_images';
        $imagesDst = APP_ROOT . '/uploads/medical_images';

        if (is_dir($imagesSrc)) {
            if (!is_dir($imagesDst)) {
                @mkdir($imagesDst, 0755, true);
            }
            $imagesRestored = self::copyDirectory($imagesSrc, $imagesDst, false);
        }

        // Optionally restore DB
        $dbRestored = false;
        if ($restoreDatabase) {
            $sqlFile = $tempDir . '/database.sql';
            if (file_exists($sqlFile)) {
                $dbRestored = self::importDatabase($sqlFile);
            }
        }

        self::cleanup($tempDir);

        // Update backups table
        $db->prepare("
            UPDATE backups 
            SET restored_at = NOW(), restored_by = ?
            WHERE backup_id = ?
        ")->execute([Auth::id(), $backupId]);

        return [
            'success'         => true,
            'images_restored' => $imagesRestored,
            'db_restored'     => $dbRestored,
            'message'         => "Restored {$imagesRestored} image files" . 
                                 ($dbRestored ? " and database" : ""),
        ];
    }

    /**
     * Verify a backup file's integrity.
     */
    public static function verify(int $backupId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM backups WHERE backup_id = ?");
        $stmt->execute([$backupId]);
        $backup = $stmt->fetch();

        if (!$backup) {
            return ['success' => false, 'message' => 'Backup not found'];
        }

        $zipPath = self::BACKUP_DIR . '/' . $backup['file_name'];

        if (!file_exists($zipPath)) {
            return ['success' => false, 'message' => 'File missing from disk'];
        }

        // Check size
        $actualSize = filesize($zipPath);
        if ((int) $backup['file_size'] !== $actualSize) {
            return [
                'success' => false,
                'message' => "Size mismatch: DB says {$backup['file_size']} bytes, disk has {$actualSize}",
            ];
        }

        // Check checksum
        if (!empty($backup['file_checksum'])) {
            $actualChecksum = hash_file('sha256', $zipPath);
            if ($actualChecksum !== $backup['file_checksum']) {
                return [
                    'success' => false,
                    'message' => 'Checksum mismatch — backup file is corrupt or modified',
                ];
            }
        }

        // Check zip integrity
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['success' => false, 'message' => 'Cannot open zip archive'];
        }

        $fileCount = $zip->numFiles;
        $hasManifest = $zip->locateName('manifest.json') !== false;
        $hasSql = $zip->locateName('database.sql') !== false;
        $zip->close();

        return [
            'success'      => true,
            'file_count'   => $fileCount,
            'has_manifest' => $hasManifest,
            'has_sql'      => $hasSql,
            'message'      => "Verified: {$fileCount} files, manifest=" . ($hasManifest ? 'yes' : 'no') . 
                              ", sql=" . ($hasSql ? 'yes' : 'no'),
        ];
    }

    // ---------- Internal helpers ----------

    private static function dumpDatabase(string $outputFile): array
    {
        $config = require APP_ROOT . '/config/database.php';

        $mysqldump = self::findMysqldump();
        if (!$mysqldump) {
            return ['success' => false, 'message' => 'mysqldump not found'];
        }

        $cmd = sprintf(
            '%s --host=%s --port=%d --user=%s %s %s > %s 2>&1',
            escapeshellcmd($mysqldump),
            escapeshellarg($config['host']),
            (int) $config['port'],
            escapeshellarg($config['username']),
            !empty($config['password']) ? '--password=' . escapeshellarg($config['password']) : '',
            escapeshellarg($config['database']),
            escapeshellarg($outputFile)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($outputFile) || filesize($outputFile) === 0) {
            return ['success' => false, 'message' => 'mysqldump exited with code ' . $returnCode];
        }

        return ['success' => true, 'message' => 'Database dumped'];
    }

    private static function importDatabase(string $sqlFile): bool
    {
        $config = require APP_ROOT . '/config/database.php';
        $mysql = self::findMysql();
        if (!$mysql) return false;

        $cmd = sprintf(
            '%s --host=%s --port=%d --user=%s %s %s < %s 2>&1',
            escapeshellcmd($mysql),
            escapeshellarg($config['host']),
            (int) $config['port'],
            escapeshellarg($config['username']),
            !empty($config['password']) ? '--password=' . escapeshellarg($config['password']) : '',
            escapeshellarg($config['database']),
            escapeshellarg($sqlFile)
        );

        exec($cmd, $output, $returnCode);
        return $returnCode === 0;
    }

    private static function findMysqldump(): ?string
    {
        $candidates = [
            '/Applications/XAMPP/xamppfiles/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/bin/mysqldump',
            trim(shell_exec('which mysqldump 2>/dev/null') ?: ''),
        ];
        foreach ($candidates as $c) {
            if ($c && is_executable($c)) return $c;
        }
        return null;
    }

    private static function findMysql(): ?string
    {
        $candidates = [
            '/Applications/XAMPP/xamppfiles/bin/mysql',
            '/usr/local/bin/mysql',
            '/usr/bin/mysql',
            trim(shell_exec('which mysql 2>/dev/null') ?: ''),
        ];
        foreach ($candidates as $c) {
            if ($c && is_executable($c)) return $c;
        }
        return null;
    }

    private static function copyDirectory(string $src, string $dst, bool $skipHtaccess = true): int
    {
        if (!is_dir($src)) return 0;
        if (!is_dir($dst)) @mkdir($dst, 0755, true);

        $count = 0;
        $items = scandir($src);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            if ($skipHtaccess && $item === '.htaccess') continue;

            $srcPath = $src . '/' . $item;
            $dstPath = $dst . '/' . $item;

            if (is_file($srcPath)) {
                if (copy($srcPath, $dstPath)) $count++;
            } elseif (is_dir($srcPath)) {
                $count += self::copyDirectory($srcPath, $dstPath, $skipHtaccess);
            }
        }
        return $count;
    }

    private static function zipDirectory(string $sourceDir, string $zipFile): array
    {
        if (!class_exists('ZipArchive')) {
            return ['success' => false, 'message' => 'ZipArchive extension not available'];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return ['success' => false, 'message' => 'Cannot create zip'];
        }

        $sourceDir = rtrim($sourceDir, '/');
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($sourceDir) + 1);

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
        return ['success' => true, 'message' => 'Zip created'];
    }

    private static function cleanup(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                self::cleanup($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    public static function humanSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }
}
