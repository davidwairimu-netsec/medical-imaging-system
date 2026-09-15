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
}