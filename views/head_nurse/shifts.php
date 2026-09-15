<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-calendar-plus"></i> Schedule New Shift</h6></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=head_nurse&action=createShift" class="row g-2 align-items-end">
            <?= CSRF::field() ?>
            <div class="col-md-4">
                <label class="form-label small">Nurse</label>
                <select name="nurse_id" class="form-select" required>
                    <option value="">Select nurse...</option>
                    <?php foreach ($nurses as $n): ?>
                        <option value="<?= $n['nurse_id'] ?>"><?= htmlspecialchars($n['first_name'].' '.$n['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Date</label>
                <input type="date" name="shift_date" class="form-control" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Shift Type</label>
                <select name="shift_type" class="form-select" required>
                    <option value="morning">Morning (07:00–15:00)</option>
                    <option value="afternoon">Afternoon (15:00–23:00)</option>
                    <option value="night">Night (23:00–07:00)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Schedule</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="bi bi-calendar-week"></i> Recent & Upcoming Shifts</h5></div>
    <div class="card-body p-0">
        <?php if (empty($shifts)): ?>
            <div class="empty-state"><i class="bi bi-calendar-x"></i><p>No shifts scheduled</p></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Date</th><th>Nurse</th><th>Type</th><th>Time</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($shifts as $s): ?>
                            <tr>
                                <td><strong><?= date('D, d M Y', strtotime($s['shift_date'])) ?></strong></td>
                                <td><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?><br><small><?= htmlspecialchars($s['staff_number']) ?></small></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($s['shift_type']) ?></span></td>
                                <td><?= date('H:i', strtotime($s['start_time'])) ?> – <?= date('H:i', strtotime($s['end_time'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $s['shift_status']==='completed'?'success':($s['shift_status']==='missed'?'danger':'primary') ?>">
                                        <?= htmlspecialchars($s['shift_status']) ?>
                                    </span>
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
