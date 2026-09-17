<?php
/**
 * Backup Model
 */
class Backup extends BaseModel
{
    protected static string $table = 'backups';
    protected static string $primaryKey = 'backup_id';

    public static function getAllWithCreator(): array
    {
        $sql = "SELECT b.*, u.full_name as created_by_name
                FROM backups b
                LEFT JOIN users u ON b.created_by = u.user_id
                ORDER BY b.created_at DESC";
        return self::db()->query($sql)->fetchAll();
    }

    public static function getRecent(int $limit = 10): array
    {
        $sql = "SELECT b.*, u.full_name as created_by_name
                FROM backups b
                LEFT JOIN users u ON b.created_by = u.user_id
                ORDER BY b.created_at DESC
                LIMIT " . (int) $limit;
        return self::db()->query($sql)->fetchAll();
    }

    public static function getFullBackups(int $limit = 20): array
    {
        $sql = "SELECT b.*, u.full_name AS created_by_name, r.full_name AS restored_by_name
                FROM backups b
                LEFT JOIN users u ON b.created_by = u.user_id
                LEFT JOIN users r ON b.restored_by = r.user_id
                WHERE b.backup_type = 'full'
                ORDER BY b.created_at DESC
                LIMIT " . (int) $limit;
        return self::db()->query($sql)->fetchAll();
    }

    public static function getTotalSize(): int
    {
        $sql = "SELECT COALESCE(SUM(file_size), 0) FROM backups WHERE backup_status = 'success'";
        return (int) self::db()->query($sql)->fetchColumn();
    }
}
