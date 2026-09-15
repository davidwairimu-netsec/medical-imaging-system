<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Nurse: <?= htmlspecialchars($nurse['first_name'].' '.$nurse['last_name']) ?></h5></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form method="POST" novalidate>
            <?= CSRF::field() ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">First Name *</label><input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($nurse['first_name']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Last Name *</label><input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($nurse['last_name']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($nurse['email'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($nurse['phone'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Department</label>
                    <select name="department_id" class="form-select" required>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['department_id'] ?>" <?= $nurse['department_id']==$d['department_id']?'selected':'' ?>><?= htmlspecialchars($d['department_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Rank</label>
                    <select name="nurse_rank" class="form-select">
                        <?php foreach (['Student','Enrolled','Registered','Senior','Chief'] as $r): ?>
                            <option value="<?= $r ?>" <?= $nurse['nurse_rank']===$r?'selected':'' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Availability</label>
                    <select name="availability_status" class="form-select">
                        <?php foreach (['available','busy','off_duty','on_leave'] as $a): ?>
                            <option value="<?= $a ?>" <?= $nurse['availability_status']===$a?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$a)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Max Patient Load</label><input type="number" name="max_patient_load" class="form-control" min="1" max="20" value="<?= (int) $nurse['max_patient_load'] ?>"></div>
                <div class="col-md-4"><label class="form-label">Record Status</label>
                    <select name="record_status" class="form-select">
                        <option value="active" <?= $nurse['record_status']==='active'?'selected':'' ?>>Active</option>
                        <option value="inactive" <?= $nurse['record_status']==='inactive'?'selected':'' ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Changes</button>
                <a href="<?= BASE_URL ?>/index.php?page=head_nurse&action=nurses" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
