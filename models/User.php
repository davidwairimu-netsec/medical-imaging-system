<?php
/**
 * User Model
 */
class User extends BaseModel
{
    protected static string $table = 'users';
    protected static string $primaryKey = 'user_id';

    public static function findByUsername(string $username): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        return $stmt->fetch() ?: null;
    }

    public static function getAllWithRoles(): array
    {
        $sql = "SELECT u.*, r.role_name 
                FROM users u
                INNER JOIN roles r ON u.role_id = r.role_id
                ORDER BY u.created_at DESC";
        return self::db()->query($sql)->fetchAll();
    }

    public static function createUser(array $data): int
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
        unset($data['password']);

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = self::db()->prepare(
            "INSERT INTO users ({$columns}) VALUES ({$placeholders})"
        );
        $stmt->execute(array_values($data));

        return (int) self::db()->lastInsertId();
    }

    public static function updateUser(int $id, array $data): bool
    {
        if (!empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
            unset($data['password']);
        } else {
            unset($data['password']);
        }

        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $values = array_values($data);
        $values[] = $id;

        $stmt = self::db()->prepare("UPDATE users SET {$set} WHERE user_id = ?");
        return $stmt->execute($values);
    }

    public static function getRoles(): array
    {
        return self::db()->query("SELECT * FROM roles ORDER BY role_id")->fetchAll();
    }

    public static function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN account_status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN account_status = 'disabled' THEN 1 ELSE 0 END) as disabled,
                    SUM(CASE WHEN account_status = 'locked' THEN 1 ELSE 0 END) as locked
                FROM users";
        return self::db()->query($sql)->fetch();
    }
}