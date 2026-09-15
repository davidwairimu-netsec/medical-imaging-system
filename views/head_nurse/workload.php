<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0"><i class="bi bi-bar-chart"></i> Nurse Workload</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Nurse</th><th>Department</th><th>Availability</th><th>Patients Assigned</th><th>Max Load</th><th>Utilization</th></tr></thead>
                <tbody>
                    <?php foreach ($workload as $w): ?>
                        <?php
                            $pct = $w['max_patient_load'] > 0 ? round(($w['patient_count'] / $w['max_patient_load']) * 100) : 0;
                            $barClass = $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($w['first_name'].' '.$w['last_name']) ?></strong><br><small><?= htmlspecialchars($w['staff_number']) ?></small></td>
                            <td><?= htmlspecialchars($w['department_name']) ?></td>
                            <td><span class="badge bg-<?= $w['availability_status']==='available'?'success':($w['availability_status']==='busy'?'warning':'secondary') ?>"><?= htmlspecialchars($w['availability_status']) ?></span></td>
                            <td><?= (int) $w['patient_count'] ?></td>
                            <td><?= (int) $w['max_patient_load'] ?></td>
                            <td style="min-width: 150px;">
                                <div class="progress" style="height: 18px;">
                                    <div class="progress-bar bg-<?= $barClass ?>" style="width: <?= $pct ?>%"><?= $pct ?>%</div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Unassigned Patients (<?= count($unassigned) ?>)</h5></div>
    <div class="card-body p-0">
        <?php if (empty($unassigned)): ?>
            <div class="empty-state"><i class="bi bi-check-circle text-success"></i><p>All patients have a nurse assigned</p></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Hospital #</th><th>Name</th><th>Gender</th><th>Phone</th></tr></thead>
                    <tbody>
                        <?php foreach ($unassigned as $p): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($p['hospital_number']) ?></code></td>
                                <td><?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?></td>
                                <td><?= htmlspecialchars($p['gender']) ?></td>
                                <td><?= htmlspecialchars($p['phone'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
