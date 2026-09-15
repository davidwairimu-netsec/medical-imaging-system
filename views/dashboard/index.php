<?php
$pageTitle = 'Dashboard';
require APP_ROOT . '/views/layouts/main.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Total Patients</span>
                <span class="stat-value"><?= number_format($patientStats['total'] ?? 0) ?></span>
                <span class="stat-sub"><?= $patientStats['active'] ?? 0 ?> active</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-images"></i></div>
            <div class="stat-content">
                <span class="stat-label">Imaging Records</span>
                <span class="stat-value"><?= number_format($imageStats['total'] ?? 0) ?></span>
                <span class="stat-sub"><?= $imageStats['archived'] ?? 0 ?> archived</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
            <div class="stat-content">
                <span class="stat-label">Active Users</span>
                <span class="stat-value"><?= number_format($userStats['active'] ?? 0) ?></span>
                <span class="stat-sub"><?= $userStats['total'] ?? 0 ?> total</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-shield-exclamation"></i></div>
            <div class="stat-content">
                <span class="stat-label">Duplicate Attempts</span>
                <span class="stat-value"><?= number_format($auditStats['duplicate_attempts'] ?? 0) ?></span>
                <span class="stat-sub"><?= $auditStats['today_events'] ?? 0 ?> events today</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Imaging Records by Type</h5>
            </div>
            <div class="card-body">
                <canvas id="imagingChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Distribution</h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="imagingPie" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Uploads</h5>
                <a href="<?= BASE_URL ?>/index.php?page=imaging" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentUploads)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>No recent uploads</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentUploads as $img): ?>
                            <a href="<?= BASE_URL ?>/index.php?page=imaging&action=view&id=<?= $img['image_id'] ?>" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-primary me-2"><?= htmlspecialchars($img['imaging_type']) ?></span>
                                        <strong><?= htmlspecialchars($img['patient_name']) ?></strong>
                                        <span class="text-muted small">(<?= htmlspecialchars($img['hospital_number']) ?>)</span>
                                    </div>
                                    <small class="text-muted"><?= date('d M Y', strtotime($img['uploaded_at'])) ?></small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-activity"></i> System Health</h5>
            </div>
            <div class="card-body">
                <div class="health-item">
                    <span><i class="bi bi-database text-success"></i> Database</span>
                    <span class="badge bg-success">Connected</span>
                </div>
                <div class="health-item">
                    <span><i class="bi bi-folder text-success"></i> Upload Directory</span>
                    <span class="badge bg-success"><?= is_writable(UPLOAD_DIR) ? 'Writable' : 'Check Permissions' ?></span>
                </div>
                <div class="health-item">
                    <span><i class="bi bi-shield-check text-success"></i> Audit Logging</span>
                    <span class="badge bg-success">Active</span>
                </div>
                <div class="health-item">
                    <span><i class="bi bi-hdd-stack text-info"></i> Storage Used</span>
                    <span class="badge bg-info"><?= number_format(($imageStats['total_size'] ?? 0) / 1024 / 1024, 2) ?> MB</span>
                </div>
                <div class="health-item">
                    <span><i class="bi bi-exclamation-triangle text-warning"></i> Failed Logins</span>
                    <span class="badge bg-warning"><?= $auditStats['failed_logins'] ?? 0 ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const chartData = <?= json_encode($chartData) ?>;

new Chart(document.getElementById('imagingChart'), {
    type: 'bar',
    data: {
        labels: chartData.labels,
        datasets: [{
            label: 'Number of Records',
            data: chartData.values,
            backgroundColor: ['#0d6efd', '#198754', '#0dcaf0', '#ffc107'],
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

new Chart(document.getElementById('imagingPie'), {
    type: 'doughnut',
    data: {
        labels: chartData.labels,
        datasets: [{
            data: chartData.values,
            backgroundColor: ['#0d6efd', '#198754', '#0dcaf0', '#ffc107']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>