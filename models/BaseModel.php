<?php
/**
 * Base Model with common database operations
 */
abstract class BaseModel
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    protected static function db(): PDO
    {
        return Database::getInstance();
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function all(string $orderBy = null): array
    {
        $sql = "SELECT * FROM " . static::$table;
        if ($orderBy) {
            $sql .= " ORDER BY " . $orderBy;
        }
        return self::db()->query($sql)->fetchAll();
    }

    public static function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO " . static::$table . " ({$columns}) VALUES ({$placeholders})";
        $stmt = self::db()->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE " . static::$table . " SET {$set} WHERE " . static::$primaryKey . " = ?";

        $values = array_values($data);
        $values[] = $id;

        $stmt = self::db()->prepare($sql);
        return $stmt->execute($values);
    }

    public static function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM " . static::$table;
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}