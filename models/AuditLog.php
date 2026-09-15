<?php
/**
 * Audit Log Model
 */
class AuditLog extends BaseModel
{
    protected static string $table = 'audit_logs';
    protected static string $primaryKey = 'log_id';

    public static function create(array $data): int
    {
        $data['ip_address'] = $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? null);
        $data['user_agent'] = $data['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? null);
        $data['created_at'] = date('Y-m-d H:i:s');

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = self::db()->prepare(
            "INSERT INTO audit_logs ({$columns}) VALUES ({$placeholders})"
        );
        $stmt->execute(array_values($data));

        return (int) self::db()->lastInsertId();
    }

    public static function getRecent(int $limit = 50, array $filters = []): array
    {
        $sql = "SELECT al.*, u.full_name as user_name, u.username
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.user_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['action'])) {
            $sql .= " AND al.action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(al.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(al.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT " . (int) $limit;

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN action = 'IMAGE_DUPLICATE_ATTEMPT' THEN 1 ELSE 0 END) as duplicate_attempts,
                    SUM(CASE WHEN action = 'USER_LOGIN_FAILED' THEN 1 ELSE 0 END) as failed_logins,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_events
                FROM audit_logs";
        return self::db()->query($sql)->fetch();
    }
}