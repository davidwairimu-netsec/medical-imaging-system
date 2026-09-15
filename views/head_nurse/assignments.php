<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-person-plus"></i> Assign Nurse to Patient</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/index.php?page=head_nurse&action=assignNursePatient">
                    <?= CSRF::field() ?>
                    <div class="mb-2">
                        <label class="form-label small">Nurse</label>
                        <select name="nurse_id" class="form-select" required>
                            <option value="">Select nurse...</option>
                            <?php foreach ($nurses as $n): ?>
                                <option value="<?= $n['nurse_id'] ?>"><?= htmlspecialchars($n['first_name'].' '.$n['last_name'].' ('.$n['staff_number'].')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Patient</label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">Select patient...</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['patient_id'] ?>"><?= htmlspecialchars($p['first_name'].' '.$p['last_name'].' ('.$p['hospital_number'].')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="normal" selected>Normal</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Notes</label>
                        <input type="text" name="notes" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Assign Nurse to Patient</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-person-check"></i> Assign Nurse to Doctor</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/index.php?page=head_nurse&action=assignNurseDoctor">
                    <?= CSRF::field() ?>
                    <div class="mb-2">
                        <label class="form-label small">Nurse</label>
                        <select name="nurse_id" class="form-select" required>
                            <option value="">Select nurse...</option>
                            <?php foreach ($nurses as $n): ?>
                                <option value="<?= $n['nurse_id'] ?>"><?= htmlspecialchars($n['first_name'].' '.$n['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Doctor</label>
                        <select name="doctor_id" class="form-select" required>
                            <option value="">Select doctor...</option>
                            <?php foreach ($doctors as $d): ?>
                                <option value="<?= $d['user_id'] ?>"><?= htmlspecialchars($d['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Notes</label>
                        <input type="text" name="notes" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Assign Nurse to Doctor</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0"><i class="bi bi-list-check"></i> Active Nurse-Patient Assignments (<?= count($npAssignments) ?>)</h5></div>
    <div class="card-body p-0">
        <?php if (empty($npAssignments)): ?>
            <div class="empty-state"><i class="bi bi-inbox"></i><p>No active assignments</p></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Nurse</th><th>Patient</th><th>Priority</th><th>Assigned By</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($npAssignments as $a): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($a['nurse_first'].' '.$a['nurse_last']) ?></strong><br><small><?= htmlspecialchars($a['staff_number']) ?></small></td>
                                <td><?= htmlspecialchars($a['patient_first'].' '.$a['patient_last']) ?><br><small><?= htmlspecialchars($a['hospital_number']) ?></small></td>
                                <td><span class="badge bg-<?= $a['priority']==='critical'?'danger':($a['priority']==='high'?'warning':'secondary') ?>"><?= htmlspecialchars($a['priority']) ?></span></td>
                                <td><small><?= htmlspecialchars($a['assigned_by_name'] ?? '-') ?></small></td>
                                <td><small><?= date('d M Y H:i', strtotime($a['assigned_at'])) ?></small></td>
                                <td>
                                    <form method="POST" action="<?= BASE_URL ?>/index.php?page=head_nurse&action=endAssignment" class="d-inline" onsubmit="return confirm('End this assignment?');">
                                        <?= CSRF::field() ?>
                                        <input type="hidden" name="id" value="<?= $a['assignment_id'] ?>">
                                        <input type="hidden" name="type" value="patient">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i> End</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="bi bi-people"></i> Active Nurse-Doctor Assignments (<?= count($ndAssignments) ?>)</h5></div>
    <div class="card-body p-0">
        <?php if (empty($ndAssignments)): ?>
            <div class="empty-state"><i class="bi bi-inbox"></i><p>No active assignments</p></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Nurse</th><th>Doctor</th><th>Assigned By</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($ndAssignments as $a): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($a['nurse_first'].' '.$a['nurse_last']) ?></strong><br><small><?= htmlspecialchars($a['staff_number']) ?></small></td>
                                <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                                <td><small><?= htmlspecialchars($a['assigned_by_name'] ?? '-') ?></small></td>
                                <td><small><?= date('d M Y H:i', strtotime($a['assigned_at'])) ?></small></td>
                                <td>
                                    <form method="POST" action="<?= BASE_URL ?>/index.php?page=head_nurse&action=endAssignment" class="d-inline" onsubmit="return confirm('End this assignment?');">
                                        <?= CSRF::field() ?>
                                        <input type="hidden" name="id" value="<?= $a['assignment_id'] ?>">
                                        <input type="hidden" name="type" value="doctor">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i> End</button>
                                    </form>
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
