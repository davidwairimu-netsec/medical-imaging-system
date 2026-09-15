<?php
/**
 * Nurse Model
 */
class Nurse extends BaseModel
{
    protected static string $table = 'nurses';
    protected static string $primaryKey = 'nurse_id';

    public static function getAllWithDepartment(): array
    {
        $sql = "SELECT n.*, d.department_name
                FROM nurses n
                INNER JOIN departments d ON n.department_id = d.department_id
                WHERE n.record_status = 'active'
                ORDER BY n.first_name, n.last_name";
        return self::db()->query($sql)->fetchAll();
    }

    public static function getWorkload(): array
    {
        $sql = "SELECT n.nurse_id, n.first_name, n.last_name, n.staff_number,
                       n.availability_status, n.max_patient_load, d.department_name,
                       (SELECT COUNT(*) FROM nurse_patient_assignments npa
                        WHERE npa.nurse_id = n.nurse_id AND npa.assignment_status = 'active') AS patient_count
                FROM nurses n
                INNER JOIN departments d ON n.department_id = d.department_id
                WHERE n.record_status = 'active'
                ORDER BY patient_count DESC";
        return self::db()->query($sql)->fetchAll();
    }

    public static function getStats(): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN availability_status = 'available' THEN 1 ELSE 0 END) AS available,
                    SUM(CASE WHEN availability_status = 'busy' THEN 1 ELSE 0 END) AS busy,
                    SUM(CASE WHEN availability_status = 'off_duty' THEN 1 ELSE 0 END) AS off_duty
                FROM nurses
                WHERE record_status = 'active'";
        return self::db()->query($sql)->fetch();
    }

    public static function getDepartments(): array
    {
        return self::db()->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();
    }
}
