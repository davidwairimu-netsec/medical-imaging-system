<?php
/**
 * Medical Imaging Controller
 */
class ImagingController
{
    public function index(): void
    {
        $this->requirePermission('view_image');

        $search = $_GET['search'] ?? '';
        $imagingType = $_GET['imaging_type'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $page = max(1, (int) ($_GET['page_num'] ?? 1));

        $filters = [
            'search' => $search,
            'imaging_type' => $imagingType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'limit' => RECORDS_PER_PAGE,
            'offset' => ($page - 1) * RECORDS_PER_PAGE,
        ];

        $images = MedicalImage::search($filters);
        $total = $this->countImages($filters);
        $totalPages = ceil($total / RECORDS_PER_PAGE);

        require APP_ROOT . '/views/imaging/index.php';
    }

    public function upload(): void
    {
        $this->requirePermission('upload_image');

        $errors = [];
        $old = [];
        $duplicateWarning = null;

        // Get patients for dropdown
        $patients = Patient::search(['status' => RECORD_ACTIVE, 'limit' => 1000]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $data = [
                'patient_id' => (int) ($_POST['patient_id'] ?? 0),
                'imaging_type' => $_POST['imaging_type'] ?? '',
                'study_date' => $_POST['study_date'] ?? '',
                'body_part' => trim($_POST['body_part'] ?? ''),
                'referring_clinician' => trim($_POST['referring_clinician'] ?? ''),
                'clinical_notes' => trim($_POST['clinical_notes'] ?? ''),
            ];

            $old = $data;

            // Validate fields
            $errors = $this->validateImageData($data);

            // Validate file
            if (empty($errors) && (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] === UPLOAD_ERR_NO_FILE)) {
                $errors['image_file'] = 'Please select an image file to upload.';
            }

            if (empty($errors) && $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
                $errors['image_file'] = 'File upload failed. Error code: ' . $_FILES['image_file']['error'];
            }

            if (empty($errors)) {
                $file = $_FILES['image_file'];

                // Validate file
                $fileErrors = $this->validateFile($file);
                if (!empty($fileErrors)) {
                    $errors = array_merge($errors, $fileErrors);
                }
            }

            if (empty($errors)) {
                $file = $_FILES['image_file'];

                // Calculate SHA-256 hash
                $hash = hash_file('sha256', $file['tmp_name']);

                // Check for duplicate
                $existing = MedicalImage::findByHash($hash);

                if ($existing) {
                    $duplicateWarning = $existing;

                    AuditLog::create([
                        'user_id' => Auth::id(),
                        'action' => AUDIT_IMAGE_DUPLICATE,
                        'entity_type' => 'medical_image',
                        'entity_id' => $existing['image_id'],
                        'description' => "Duplicate image upload attempt. Existing image ID: {$existing['image_id']}",
                    ]);
                } else {
                    // Save file with secure random name
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $secureName = bin2hex(random_bytes(16)) . '.' . $extension;
                    $filePath = UPLOAD_DIR . $secureName;

                    if (!is_dir(UPLOAD_DIR)) {
                        mkdir(UPLOAD_DIR, 0755, true);
                    }

                    if (move_uploaded_file($file['tmp_name'], $filePath)) {
                        $imageId = MedicalImage::create([
                            'patient_id' => $data['patient_id'],
                            'imaging_type' => $data['imaging_type'],
                            'study_date' => $data['study_date'],
                            'body_part' => $data['body_part'],
                            'referring_clinician' => $data['referring_clinician'],
                            'clinical_notes' => $data['clinical_notes'],
                            'file_name' => $secureName,
                            'original_file_name' => $file['name'],
                            'file_path' => 'uploads/medical_images/' . $secureName,
                            'file_mime_type' => mime_content_type($filePath),
                            'file_size' => $file['size'],
                            'file_hash' => $hash,
                            'uploaded_by' => Auth::id(),
                        ]);

                        AuditLog::create([
                            'user_id' => Auth::id(),
                            'action' => AUDIT_IMAGE_UPLOAD,
                            'entity_type' => 'medical_image',
                            'entity_id' => $imageId,
                            'description' => "Uploaded {$data['imaging_type']} image for patient ID {$data['patient_id']}",
                        ]);

                        Session::flash('success', 'Medical image uploaded successfully.');
                        header('Location: ' . BASE_URL . '/index.php?page=imaging&action=view&id=' . $imageId);
                        exit;
                    } else {
                        $errors['image_file'] = 'Failed to save uploaded file. Check directory permissions.';
                    }
                }
            }
        }

        require APP_ROOT . '/views/imaging/upload.php';
    }

    public function view(): void
    {
        $this->requirePermission('view_image');

        $id = (int) ($_GET['id'] ?? 0);

        $stmt = Database::getInstance()->prepare("
            SELECT mi.*, 
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   p.hospital_number,
                   p.patient_id,
                   u.full_name as uploaded_by_name
            FROM medical_images mi
            INNER JOIN patients p ON mi.patient_id = p.patient_id
            LEFT JOIN users u ON mi.uploaded_by = u.user_id
            WHERE mi.image_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $image = $stmt->fetch();

        if (!$image) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => AUDIT_IMAGE_VIEW,
            'entity_type' => 'medical_image',
            'entity_id' => $id,
            'description' => "Viewed image record ID: {$id}",
        ]);

        require APP_ROOT . '/views/imaging/view.php';
    }

    public function serve(): void
    {
        $this->requirePermission('view_image');

        $id = (int) ($_GET['id'] ?? 0);
        $image = MedicalImage::find($id);

        if (!$image) {
            http_response_code(404);
            exit;
        }

        $filePath = APP_ROOT . '/' . $image['file_path'];

        if (!file_exists($filePath)) {
            http_response_code(404);
            exit;
        }

        // Serve file securely
        header('Content-Type: ' . $image['file_mime_type']);
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: inline; filename="' . basename($image['file_name']) . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=3600');

        readfile($filePath);
        exit;
    }

    public function download(): void
    {
        $this->requirePermission('view_image');

        $id = (int) ($_GET['id'] ?? 0);
        $image = MedicalImage::find($id);

        if (!$image) {
            http_response_code(404);
            exit;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => AUDIT_IMAGE_DOWNLOAD,
            'entity_type' => 'medical_image',
            'entity_id' => $id,
            'description' => "Downloaded image: {$image['original_file_name']}",
        ]);

        $filePath = APP_ROOT . '/' . $image['file_path'];

        if (!file_exists($filePath)) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: ' . $image['file_mime_type']);
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: attachment; filename="' . $image['original_file_name'] . '"');
        readfile($filePath);
        exit;
    }

    public function archive(): void
    {
        $this->requirePermission('archive_image');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        CSRF::verify();

        $id = (int) ($_POST['id'] ?? 0);
        $image = MedicalImage::find($id);

        if (!$image) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }

        MedicalImage::update($id, ['record_status' => RECORD_ARCHIVED]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => AUDIT_IMAGE_ARCHIVE,
            'entity_type' => 'medical_image',
            'entity_id' => $id,
            'description' => "Archived image record ID: {$id}",
        ]);

        Session::flash('success', 'Image record archived successfully.');
        header('Location: ' . BASE_URL . '/index.php?page=imaging');
        exit;
    }

    private function validateImageData(array $data): array
    {
        $errors = [];

        if (empty($data['patient_id'])) {
            $errors['patient_id'] = 'Please select a patient.';
        } elseif (!Patient::find($data['patient_id'])) {
            $errors['patient_id'] = 'Selected patient does not exist.';
        }

        if (empty($data['imaging_type']) || !in_array($data['imaging_type'], IMAGING_TYPES, true)) {
            $errors['imaging_type'] = 'Please select a valid imaging type.';
        }

        if (empty($data['study_date'])) {
            $errors['study_date'] = 'Study date is required.';
        } elseif (strtotime($data['study_date']) > time()) {
            $errors['study_date'] = 'Study date cannot be in the future.';
        }

        if (empty($data['body_part'])) {
            $errors['body_part'] = 'Body part is required.';
        }

        return $errors;
    }

    private function validateFile(array $file): array
    {
        $errors = [];

        if ($file['size'] > MAX_FILE_SIZE) {
            $errors['image_file'] = 'File size exceeds maximum allowed (' . (MAX_FILE_SIZE / 1024 / 1024) . ' MB).';
            return $errors;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
            $errors['image_file'] = 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS);
            return $errors;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, ALLOWED_MIME_TYPES, true)) {
            $errors['image_file'] = 'Invalid file content type. Only JPEG and PNG images are allowed.';
            return $errors;
        }

        // Verify it's a real image
        if (!@getimagesize($file['tmp_name'])) {
            $errors['image_file'] = 'The uploaded file is not a valid image.';
        }

        return $errors;
    }

    private function countImages(array $filters): int
    {
        $sql = "SELECT COUNT(*) FROM medical_images mi 
                INNER JOIN patients p ON mi.patient_id = p.patient_id WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.hospital_number LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? 
                          OR mi.body_part LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        if (!empty($filters['imaging_type'])) {
            $sql .= " AND mi.imaging_type = ?";
            $params[] = $filters['imaging_type'];
        }

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function requirePermission(string $permission): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
        if (!Auth::hasPermission($permission)) {
            http_response_code(403);
            require APP_ROOT . '/views/errors/403.php';
            exit;
        }
    }
}