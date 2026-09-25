<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<?php if ($nurse): ?>
    <div class="alert alert-info mb-4">
        <i class="bi bi-person-badge"></i>
        <strong>Welcome, <?= htmlspecialchars($nurse['first_name'] . ' ' . $nurse['last_name']) ?></strong>
        — <?= htmlspecialchars($nurse['nurse_rank']) ?> 
        (<?= htmlspecialchars($nurse['department_name']) ?>)
        <span class="badge bg-<?= $nurse['availability_status'] === 'available' ? 'success' : 'warning' ?> ms-2">
            <?= htmlspecialchars($nurse['availability_status']) ?>
        </span>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">My Assigned Patients</span>
                <span class="stat-value"><?= count($assignments) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="bi bi-shield-lock"></i></div>
            <div class="stat-content">
                <span class="stat-label">Access Level</span>
                <span class="stat-value">Read-Only</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Available</span>
                <span class="stat-value"><?= $nurse['availability_status'] ?? 'N/A' ?></span>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-warning small">
    <i class="bi bi-info-circle"></i>
    <strong>Your access is read-only.</strong>
    You can view assigned patients' records and medication schedules, but you cannot modify any data.
    If you need to reassign a patient, contact the Head Nurse.
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-people"></i> Assigned Patients (<?= count($assignments) ?>)</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($assignments)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>No assigned patients</h5>
                <p>You currently have no active patient assignments.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Hospital No.</th>
                            <th>Age / Gender</th>
                            <th>Assigned Nurse</th>
                            <th>Priority</th>
                            <th>Assigned</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php if (isset($a['patient_first'])): ?>
                                            <?= htmlspecialchars($a['patient_first'] . ' ' . $a['patient_last']) ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?>
                                        <?php endif; ?>
                                    </strong>
                                </td>
                                <td><code><?= htmlspecialchars($a['hospital_number']) ?></code></td>
                                <td>
                                    <?php
                                        $dob = $a['date_of_birth'] ?? null;
                                        $age = $dob ? (new DateTime($dob))->diff(new DateTime())->y : '?';
                                    ?>
                                    <?= $age ?> / <?= htmlspecialchars($a['gender'] ?? '-') ?>
                                </td>
                                <td>
                                    <?php if (isset($a['nurse_first'])): ?>
                                        <?= htmlspecialchars($a['nurse_first'] . ' ' . $a['nurse_last']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= ($a['priority'] ?? 'normal') === 'critical' ? 'danger' : (($a['priority'] ?? 'normal') === 'high' ? 'warning' : 'secondary') ?>">
                                        <?= htmlspecialchars($a['priority'] ?? 'normal') ?>
                                    </span>
                                </td>
                                <td><small><?= date('d M Y H:i', strtotime($a['assigned_at'])) ?></small></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/index.php?page=nurse&action=patient&id=<?= $a['patient_id'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View Record
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
