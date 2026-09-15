<?php
/**
 * Dashboard Controller
 */
class DashboardController
{
    public function index(): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        $patientStats = Patient::getStats();
        $imageStats = MedicalImage::getStats();
        $userStats = User::getStats();
        $auditStats = AuditLog::getStats();
        $recentUploads = MedicalImage::getRecentUploads(5);

        // Chart data
        $chartData = [
            'labels' => ['X-ray', 'CT', 'MRI', 'Ultrasound'],
            'values' => [
                (int) ($imageStats['xray_count'] ?? 0),
                (int) ($imageStats['ct_count'] ?? 0),
                (int) ($imageStats['mri_count'] ?? 0),
                (int) ($imageStats['ultrasound_count'] ?? 0),
            ],
        ];

        require APP_ROOT . '/views/dashboard/index.php';
    }
}