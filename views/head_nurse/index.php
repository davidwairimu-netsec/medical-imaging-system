<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Total Nurses</span>
                <span class="stat-value"><?= $stats['total_nurses'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Available</span>
                <span class="stat-value"><?= $stats['available'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-content">
                <span class="stat-label">Busy</span>
                <span class="stat-value"><?= $stats['busy'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="bi bi-person-check-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Assignments</span>
                <span class="stat-value"><?= $stats['assignments'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Unassigned</span>
                <span class="stat-value"><?= $stats['unassigned'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
            <div class="stat-content">
                <span class="stat-label">Shifts Today</span>
                <span class="stat-value"><?= $stats['shifts_today'] ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-bar-chart-fill"></i> Nurse Workload</h5>
                <a href="<?= BASE_URL ?>/index.php?page=head_nurse&action=workload" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Nurse</th><th>Department</th><th>Status</th><th>Load</th></tr></thead>
                        <tbody>
                            <?php foreach (array_slice($workload, 0, 8) as $w): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($w['first_name'].' '.$w['last_name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($w['staff_number']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($w['department_name']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $w['availability_status']==='available'?'success':($w['availability_status']==='busy'?'warning':'secondary') ?>">
                                            <?= htmlspecialchars($w['availability_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= (int) $w['patient_count'] ?> / <?= (int) $w['max_patient_load'] ?>
                                        <?php if ($w['patient_count'] >= $w['max_patient_load']): ?>
                                            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Today's Shifts</h5>
                <a href="<?= BASE_URL ?>/index.php?page=head_nurse&action=shifts" class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($todayShifts)): ?>
                    <div class="empty-state"><i class="bi bi-calendar-x"></i><p>No shifts today</p></div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($todayShifts as $s): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></strong><br>
                                        <small><?= htmlspecialchars($s['staff_number']) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-info"><?= htmlspecialchars($s['shift_type']) ?></span><br>
                                        <small><?= date('H:i', strtotime($s['start_time'])) ?>–<?= date('H:i', strtotime($s['end_time'])) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
