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
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');

        $db = self::db();

        // Get the hash of the most recent row (to build chain)
        $prevRow = $db->query("SELECT row_hash FROM audit_logs ORDER BY log_id DESC LIMIT 1")->fetch();
        $previousHash = $prevRow['row_hash'] ?? str_repeat('0', 64);

        // Compute this row's hash
        $hashInput = $previousHash
                   . '|' . ($data['user_id'] ?? 'null')
                   . '|' . ($data['action'] ?? '')
                   . '|' . ($data['entity_type'] ?? '')
                   . '|' . ($data['entity_id'] ?? 'null')
                   . '|' . ($data['description'] ?? '')
                   . '|' . ($data['ip_address'] ?? '')
                   . '|' . ($data['created_at'] ?? '');
        $rowHash = hash('sha256', $hashInput);

        $data['previous_hash'] = $previousHash;
        $data['row_hash'] = $rowHash;

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = $db->prepare(
            "INSERT INTO audit_logs ({$columns}) VALUES ({$placeholders})"
        );
        $stmt->execute(array_values($data));

        return (int) $db->lastInsertId();
    }

    /**
     * Verify the integrity of the audit log chain.
     * Returns array with valid (bool) and broken rows (if any).
     */
    public static function verifyChain(): array
    {
        $db = self::db();
        $rows = $db->query("SELECT * FROM audit_logs ORDER BY log_id ASC")->fetchAll();

        $broken = [];
        $previousHash = str_repeat('0', 64);
        $verified = 0;

        foreach ($rows as $row) {
            // Recompute what this row's hash should be
            $hashInput = $previousHash
                       . '|' . ($row['user_id'] ?? 'null')
                       . '|' . ($row['action'] ?? '')
                       . '|' . ($row['entity_type'] ?? '')
                       . '|' . ($row['entity_id'] ?? 'null')
                       . '|' . ($row['description'] ?? '')
                       . '|' . ($row['ip_address'] ?? '')
                       . '|' . ($row['created_at'] ?? '');
            $expectedHash = hash('sha256', $hashInput);

            if ($row['previous_hash'] !== $previousHash) {
                $broken[] = [
                    'log_id' => $row['log_id'],
                    'issue' => 'previous_hash mismatch',
                    'expected_previous' => $previousHash,
                    'stored_previous' => $row['previous_hash'],
                ];
            } elseif ($row['row_hash'] !== $expectedHash) {
                $broken[] = [
                    'log_id' => $row['log_id'],
                    'issue' => 'row_hash mismatch (data was modified)',
                    'stored_hash' => $row['row_hash'],
                    'expected_hash' => $expectedHash,
                ];
            } else {
                $verified++;
            }

            $previousHash = $row['row_hash'];
        }

        return [
            'valid' => empty($broken),
            'total' => count($rows),
            'verified' => $verified,
            'broken' => $broken,
        ];
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