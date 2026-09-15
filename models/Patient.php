<?php
/**
 * Patient Model
 */
class Patient extends BaseModel
{
    protected static string $table = 'patients';
    protected static string $primaryKey = 'patient_id';

    public static function getAllWithRegistrationInfo(): array
    {
        $sql = "SELECT p.*, 
                       CONCAT(u.full_name) as registered_by_name,
                       (SELECT COUNT(*) FROM medical_images mi WHERE mi.patient_id = p.patient_id AND mi.record_status = 'active') as image_count
                FROM patients p
                LEFT JOIN users u ON p.registered_by = u.user_id
                ORDER BY p.created_at DESC";
        return self::db()->query($sql)->fetchAll();
    }

    public static function search(array $filters = []): array
    {
        $sql = "SELECT p.*, 
                       CONCAT(u.full_name) as registered_by_name,
                       (SELECT COUNT(*) FROM medical_images mi WHERE mi.patient_id = p.patient_id AND mi.record_status = 'active') as image_count
                FROM patients p
                LEFT JOIN users u ON p.registered_by = u.user_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.hospital_number LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? 
                          OR p.national_id LIKE ? OR p.phone LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.record_status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['gender'])) {
            $sql .= " AND p.gender = ?";
            $params[] = $filters['gender'];
        }

        $sql .= " ORDER BY p.created_at DESC";

        // Pagination
        if (!empty($filters['limit'])) {
            $offset = $filters['offset'] ?? 0;
            $sql .= " LIMIT " . (int) $filters['limit'] . " OFFSET " . (int) $offset;
        }

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getWithImages(int $patientId): ?array
    {
        $patient = self::find($patientId);
        if (!$patient) return null;

        $stmt = self::db()->prepare("
            SELECT mi.*, u.full_name as uploaded_by_name 
            FROM medical_images mi
            LEFT JOIN users u ON mi.uploaded_by = u.user_id
            WHERE mi.patient_id = ? AND mi.record_status = 'active'
            ORDER BY mi.study_date DESC, mi.uploaded_at DESC
        ");
        $stmt->execute([$patientId]);
        $patient['images'] = $stmt->fetchAll();

        return $patient;
    }

    public static function checkDuplicate(string $hospitalNumber, ?string $nationalId, ?string $phone): array
    {
        $duplicates = [];

        if ($hospitalNumber) {
            $stmt = self::db()->prepare(
                "SELECT patient_id, first_name, last_name, hospital_number 
                 FROM patients WHERE hospital_number = ? LIMIT 1"
            );
            $stmt->execute([$hospitalNumber]);
            if ($row = $stmt->fetch()) {
                $duplicates['hospital_number'] = $row;
            }
        }

        if ($nationalId) {
            $stmt = self::db()->prepare(
                "SELECT patient_id, first_name, last_name, national_id 
                 FROM patients WHERE national_id = ? LIMIT 1"
            );
            $stmt->execute([$nationalId]);
            if ($row = $stmt->fetch()) {
                $duplicates['national_id'] = $row;
            }
        }

        return $duplicates;
    }

    public static function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN record_status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN record_status = 'archived' THEN 1 ELSE 0 END) as archived
                FROM patients";
        return self::db()->query($sql)->fetch();
    }
}