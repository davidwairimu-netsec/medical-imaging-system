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
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/index.php?page=head_nurse&action=editNurse&id=<?= $n['nurse_id'] ?>" 
                                           class="btn btn-outline-primary" title="Edit nurse">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if (empty($n['user_id'])): ?>
                                            <button type="button" class="btn btn-outline-success" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#createLoginModal<?= $n['nurse_id'] ?>"
                                                    title="Create login account">
                                                <i class="bi bi-key"></i> Create Login
                                            </button>
                                        <?php else: ?>
                                            <span class="badge bg-success align-self-center" 
                                                  title="Has login: <?= htmlspecialchars($n['username'] ?? '') ?>">
                                                <i class="bi bi-check-circle"></i> Has Login
                                            </span>
                                            <button type="button" class="btn btn-outline-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#resetPwdModal<?= $n['nurse_id'] ?>"
                                                    title="Reset password">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php foreach ($nurses as $n): ?>
    <?php if (empty($n['user_id'])): ?>
    <div class="modal fade" id="createLoginModal<?= $n['nurse_id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?= BASE_URL ?>/index.php?page=head_nurse&action=createNurseLogin">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="nurse_id" value="<?= $n['nurse_id'] ?>">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-key"></i> Create Login Account</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Create a login for <strong><?= htmlspecialchars($n['first_name'] . ' ' . $n['last_name']) ?></strong> 
                           (<?= htmlspecialchars($n['staff_number']) ?>)</p>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" 
                                   value="<?= htmlspecialchars(strtolower($n['first_name'] . '.' . $n['last_name'])) ?>">
                            <div class="form-text">Suggested: firstname.lastname</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Initial Password</label>
                            <input type="text" name="password" class="form-control" value="ChangeMe123!">
                            <div class="form-text">Nurse should change this after first login</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Create Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="modal fade" id="resetPwdModal<?= $n['nurse_id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?= BASE_URL ?>/index.php?page=head_nurse&action=resetNursePassword">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="nurse_id" value="<?= $n['nurse_id'] ?>">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise"></i> Reset Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Reset password for <strong><?= htmlspecialchars($n['first_name'] . ' ' . $n['last_name']) ?></strong></p>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="text" name="new_password" class="form-control" value="ChangeMe123!">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
