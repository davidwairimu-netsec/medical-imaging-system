<?php
/**
 * Medication Model
 * Patient medication records (read by nurses, prescribed by doctors)
 */
class Medication extends BaseModel
{
    protected static string $table = 'medications';
    protected static string $primaryKey = 'medication_id';

    public static function getForPatient(int $patientId): array
    {
        $sql = "SELECT m.*, 
                       CONCAT(u.full_name) AS prescriber_name
                FROM medications m
                LEFT JOIN users u ON m.prescribed_by = u.user_id
                WHERE m.patient_id = ?
                  AND m.record_status = 'active'
                ORDER BY m.start_date DESC";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = self::db()->prepare(
            "INSERT INTO medications ({$columns}) VALUES ({$placeholders})"
        );
        $stmt->execute(array_values($data));

        return (int) self::db()->lastInsertId();
    }
}
