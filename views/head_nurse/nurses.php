<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="head_nurse">
            <input type="hidden" name="action" value="nurses">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Name or staff number">
            </div>
            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select name="department" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['department_id'] ?>" <?= ($_GET['department']??'')==$d['department_id']?'selected':'' ?>><?= htmlspecialchars($d['department_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Availability</label>
                <select name="availability" class="form-select">
                    <option value="">All</option>
                    <option value="available" <?= ($_GET['availability']??'')==='available'?'selected':'' ?>>Available</option>
                    <option value="busy" <?= ($_GET['availability']??'')==='busy'?'selected':'' ?>>Busy</option>
                    <option value="off_duty" <?= ($_GET['availability']??'')==='off_duty'?'selected':'' ?>>Off Duty</option>
                    <option value="on_leave" <?= ($_GET['availability']??'')==='on_leave'?'selected':'' ?>>On Leave</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
            </div>
            <div class="col-md-1">
                <a href="<?= BASE_URL ?>/index.php?page=head_nurse&action=createNurse" class="btn btn-success w-100"><i class="bi bi-plus"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people"></i> Nurses (<?= count($nurses) ?>)</h5>
        <a href="<?= BASE_URL ?>/index.php?page=head_nurse&action=createNurse" class="btn btn-sm btn-success">
            <i class="bi bi-plus-circle"></i> Add Nurse
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($nurses)): ?>
            <div class="empty-state"><i class="bi bi-inbox"></i><h5>No nurses found</h5></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Staff #</th><th>Name</th><th>Department</th><th>Rank</th><th>Availability</th><th>Max Load</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($nurses as $n): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($n['staff_number']) ?></code></td>
                                <td><strong><?= htmlspecialchars($n['first_name'].' '.$n['last_name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($n['email'] ?? '') ?></small></td>
                                <td><?= htmlspecialchars($n['department_name']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($n['nurse_rank']) ?></span></td>
                                <td>
                                    <span class="badge bg-<?= $n['availability_status']==='available'?'success':($n['availability_status']==='busy'?'warning':'secondary') ?>">
                                        <?= htmlspecialchars($n['availability_status']) ?>
                                    </span>
                                </td>
                                <td><?= (int) $n['max_patient_load'] ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/index.php?page=head_nurse&action=editNurse&id=<?= $n['nurse_id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
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
