<?php
/**
 * Medical Image Model
 */
class MedicalImage extends BaseModel
{
    protected static string $table = 'medical_images';
    protected static string $primaryKey = 'image_id';

    public static function getAllWithDetails(): array
    {
        $sql = "SELECT mi.*, 
                       CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       p.hospital_number,
                       u.full_name as uploaded_by_name
                FROM medical_images mi
                INNER JOIN patients p ON mi.patient_id = p.patient_id
                LEFT JOIN users u ON mi.uploaded_by = u.user_id
                ORDER BY mi.uploaded_at DESC";
        return self::db()->query($sql)->fetchAll();
    }

    public static function search(array $filters = []): array
    {
        $sql = "SELECT mi.*, 
                       CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       p.hospital_number,
                       u.full_name as uploaded_by_name
                FROM medical_images mi
                INNER JOIN patients p ON mi.patient_id = p.patient_id
                LEFT JOIN users u ON mi.uploaded_by = u.user_id
                WHERE mi.record_status = 'active'";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.hospital_number LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? 
                          OR mi.body_part LIKE ? OR mi.referring_clinician LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$term, $term, $term, $term, $term]);
        }

        if (!empty($filters['imaging_type'])) {
            $sql .= " AND mi.imaging_type = ?";
            $params[] = $filters['imaging_type'];
        }

        if (!empty($filters['body_part'])) {
            $sql .= " AND mi.body_part LIKE ?";
            $params[] = '%' . $filters['body_part'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND mi.study_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND mi.study_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND mi.record_status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY mi.study_date DESC, mi.uploaded_at DESC";

        if (!empty($filters['limit'])) {
            $offset = $filters['offset'] ?? 0;
            $sql .= " LIMIT " . (int) $filters['limit'] . " OFFSET " . (int) $offset;
        }

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findByHash(string $hash): ?array
    {
        $stmt = self::db()->prepare("
            SELECT mi.*, 
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   p.hospital_number
            FROM medical_images mi
            INNER JOIN patients p ON mi.patient_id = p.patient_id
            WHERE mi.file_hash = ?
            LIMIT 1
        ");
        $stmt->execute([$hash]);
        return $stmt->fetch() ?: null;
    }

    public static function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN imaging_type = 'X-ray' THEN 1 ELSE 0 END) as xray_count,
                    SUM(CASE WHEN imaging_type = 'CT' THEN 1 ELSE 0 END) as ct_count,
                    SUM(CASE WHEN imaging_type = 'MRI' THEN 1 ELSE 0 END) as mri_count,
                    SUM(CASE WHEN imaging_type = 'Ultrasound' THEN 1 ELSE 0 END) as ultrasound_count,
                    SUM(CASE WHEN record_status = 'archived' THEN 1 ELSE 0 END) as archived,
                    SUM(file_size) as total_size
                FROM medical_images
                WHERE record_status = 'active'";
        return self::db()->query($sql)->fetch();
    }

    public static function getRecentUploads(int $limit = 5): array
    {
        $sql = "SELECT mi.*, 
                       CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       p.hospital_number
                FROM medical_images mi
                INNER JOIN patients p ON mi.patient_id = p.patient_id
                WHERE mi.record_status = 'active'
                ORDER BY mi.uploaded_at DESC
                LIMIT " . (int) $limit;
        return self::db()->query($sql)->fetchAll();
    }
}
