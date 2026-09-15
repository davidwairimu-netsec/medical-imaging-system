<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-person-check-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Active Patient Assignments</span>
                <span class="stat-value"><?= (int) $assignmentStats['active_np'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-people-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Active Doctor Assignments</span>
                <span class="stat-value"><?= (int) $assignmentStats['active_nd'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Available Nurses</span>
                <span class="stat-value"><?= (int) $assignmentStats['available'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-content">
                <span class="stat-label">Busy Nurses</span>
                <span class="stat-value"><?= (int) $assignmentStats['busy'] ?></span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="bi bi-building"></i> Department Summary</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Department</th><th>Total Nurses</th><th>Available</th><th>Availability Rate</th></tr></thead>
                <tbody>
                    <?php foreach ($departmentStats as $d): ?>
                        <?php $rate = $d['nurse_count'] > 0 ? round(($d['available_count'] / $d['nurse_count']) * 100) : 0; ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($d['department_name']) ?></strong></td>
                            <td><?= (int) $d['nurse_count'] ?></td>
                            <td><?= (int) $d['available_count'] ?></td>
                            <td>
                                <div class="progress" style="height: 18px; min-width: 120px;">
                                    <div class="progress-bar bg-<?= $rate >= 60 ? 'success' : 'warning' ?>" style="width: <?= $rate ?>%"><?= $rate ?>%</div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
