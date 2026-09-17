<?php
/**
 * Storage Integrity Checker
 */
class StorageIntegrity
{
    public const STATUS_OK = 'ok';
    public const STATUS_MISSING = 'missing';
    public const STATUS_EMPTY = 'empty';
    public const STATUS_SIZE_MISMATCH = 'size_mismatch';
    public const STATUS_HASH_MISMATCH = 'hash_mismatch';

    public static function checkImage(array $image): array
    {
        $path = APP_ROOT . '/' . ltrim($image['file_path'], '/');

        if (!file_exists($path)) {
            return [
                'status' => self::STATUS_MISSING,
                'message' => 'File not found on disk',
                'actual_size' => 0,
            ];
        }

        if (!is_file($path) || !is_readable($path)) {
            return [
                'status' => self::STATUS_MISSING,
                'message' => 'Path exists but is not a readable file',
                'actual_size' => 0,
            ];
        }

        $actualSize = (int) filesize($path);

        if ($actualSize === 0) {
            return [
                'status' => self::STATUS_EMPTY,
                'message' => 'File exists but is 0 bytes',
                'actual_size' => 0,
            ];
        }

        if (isset($image['file_size']) && (int) $image['file_size'] !== $actualSize) {
            return [
                'status' => self::STATUS_SIZE_MISMATCH,
                'message' => 'Size mismatch: DB=' . (int) $image['file_size'] . ', disk=' . $actualSize,
                'actual_size' => $actualSize,
            ];
        }

        return [
            'status' => self::STATUS_OK,
            'message' => 'File verified',
            'actual_size' => $actualSize,
        ];
    }

    public static function verifyHash(array $image): array
    {
        $path = APP_ROOT . '/' . ltrim($image['file_path'], '/');

        if (!file_exists($path) || !is_readable($path)) {
            return ['status' => self::STATUS_MISSING, 'message' => 'Cannot verify hash: file missing'];
        }

        $actualHash = hash_file('sha256', $path);

        if ($actualHash !== $image['file_hash']) {
            return [
                'status' => self::STATUS_HASH_MISMATCH,
                'message' => 'Hash mismatch',
                'expected' => $image['file_hash'],
                'actual'   => $actualHash,
            ];
        }

        return ['status' => self::STATUS_OK, 'message' => 'Hash verified'];
    }

    public static function auditAll(bool $includeArchived = false): array
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM medical_images";
        if (!$includeArchived) {
            $sql .= " WHERE record_status = 'active'";
        }
        $rows = $db->query($sql)->fetchAll();

        $missing = []; $empty = []; $ok = []; $mismatch = [];

        foreach ($rows as $row) {
            $result = self::checkImage($row);
            $row['_integrity'] = $result;
            switch ($result['status']) {
                case self::STATUS_MISSING: $missing[] = $row; break;
                case self::STATUS_EMPTY:   $empty[]   = $row; break;
                case self::STATUS_SIZE_MISMATCH:
                case self::STATUS_OK:      $ok[]      = $row; break;
                default:                   $mismatch[] = $row; break;
            }
        }

        return [
            'missing'  => $missing,
            'empty'    => $empty,
            'ok'       => $ok,
            'mismatch' => $mismatch,
            'orphaned' => self::findOrphanedFiles(),
            'total_db' => count($rows),
        ];
    }

    public static function findOrphanedFiles(): array
    {
        $db = Database::getInstance();
        $uploadDir = APP_ROOT . '/uploads/medical_images/';

        if (!is_dir($uploadDir)) return [];

        $files = glob($uploadDir . '*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
        if (empty($files)) return [];

        $known = $db->query("SELECT file_name FROM medical_images")->fetchAll(PDO::FETCH_COLUMN);
        $knownSet = array_flip($known);

        $orphaned = [];
        foreach ($files as $f) {
            $basename = basename($f);
            if (!isset($knownSet[$basename])) {
                $orphaned[] = [
                    'file_name' => $basename,
                    'file_size' => filesize($f),
                    'modified'  => date('Y-m-d H:i:s', filemtime($f)),
                ];
            }
        }
        return $orphaned;
    }

    public static function getStats(): array
    {
        $db = Database::getInstance();
        $totalImages = (int) $db->query("SELECT COUNT(*) FROM medical_images WHERE record_status = 'active'")->fetchColumn();
        $archivedImages = (int) $db->query("SELECT COUNT(*) FROM medical_images WHERE record_status = 'archived'")->fetchColumn();

        $uploadDir = APP_ROOT . '/uploads/medical_images/';
        $diskFiles = 0;
        $diskBytes = 0;

        if (is_dir($uploadDir)) {
            $files = glob($uploadDir . '*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE) ?: [];
            $diskFiles = count($files);
            foreach ($files as $f) {
                $diskBytes += (int) filesize($f);
            }
        }

        return [
            'active_db'     => $totalImages,
            'archived_db'   => $archivedImages,
            'disk_files'    => $diskFiles,
            'disk_bytes'    => $diskBytes,
            'disk_human'    => self::humanSize($diskBytes),
            'is_consistent' => ($totalImages === $diskFiles),
        ];
    }

    private static function humanSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }
}
