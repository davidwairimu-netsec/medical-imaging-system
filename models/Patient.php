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

    /**
     * Enhanced duplicate detection.
     * Returns array of matches keyed by match type:
     *   - exact_hospital_number (same hospital number)
     *   - exact_national_id (same national ID)
     *   - possible_name_dob (same first+last name AND same DOB)
     *   - possible_phone (same phone number)
     * Each value is ['confidence' => 'exact|high|medium', 'record' => [...]]
     */
    public static function checkDuplicate(
        string $hospitalNumber,
        ?string $nationalId = null,
        ?string $phone = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $dateOfBirth = null,
        ?int $excludeId = null
    ): array {
        $db = self::db();
        $matches = [];

        // 1. Exact hospital number match
        if ($hospitalNumber) {
            $sql = "SELECT patient_id, first_name, last_name, hospital_number, date_of_birth, phone
                    FROM patients WHERE hospital_number = ?";
            $params = [$hospitalNumber];
            if ($excludeId) {
                $sql .= " AND patient_id != ?";
                $params[] = $excludeId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            if ($row = $stmt->fetch()) {
                $matches['exact_hospital_number'] = [
                    'confidence' => 'exact',
                    'message' => 'Same hospital number already exists',
                    'record' => $row,
                ];
            }
        }

        // 2. Exact national ID match
        if ($nationalId) {
            $sql = "SELECT patient_id, first_name, last_name, hospital_number, national_id, date_of_birth
                    FROM patients WHERE national_id = ?";
            $params = [$nationalId];
            if ($excludeId) {
                $sql .= " AND patient_id != ?";
                $params[] = $excludeId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            if ($row = $stmt->fetch()) {
                $matches['exact_national_id'] = [
                    'confidence' => 'exact',
                    'message' => 'Same national ID already exists',
                    'record' => $row,
                ];
            }
        }

        // 3. Possible duplicate: same first+last name AND same DOB
        if ($firstName && $lastName && $dateOfBirth) {
            $sql = "SELECT patient_id, first_name, last_name, hospital_number, date_of_birth, phone
                    FROM patients
                    WHERE LOWER(first_name) = LOWER(?)
                      AND LOWER(last_name) = LOWER(?)
                      AND date_of_birth = ?";
            $params = [$firstName, $lastName, $dateOfBirth];
            if ($excludeId) {
                $sql .= " AND patient_id != ?";
                $params[] = $excludeId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            if ($row = $stmt->fetch()) {
                $matches['possible_name_dob'] = [
                    'confidence' => 'high',
                    'message' => 'Another patient has the same name and date of birth',
                    'record' => $row,
                ];
            }
        }

        // 4. Possible duplicate: same phone number
        if ($phone) {
            $sql = "SELECT patient_id, first_name, last_name, hospital_number, phone
                    FROM patients WHERE phone = ?";
            $params = [$phone];
            if ($excludeId) {
                $sql .= " AND patient_id != ?";
                $params[] = $excludeId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            if ($row = $stmt->fetch()) {
                $matches['possible_phone'] = [
                    'confidence' => 'medium',
                    'message' => 'Another patient has the same phone number',
                    'record' => $row,
                ];
            }
        }

        return $matches;
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