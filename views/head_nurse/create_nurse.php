<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="bi bi-person-plus"></i> Add Nurse</h5></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form method="POST" novalidate>
            <?= CSRF::field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Staff Number *</label>
                    <input type="text" name="staff_number" class="form-control" required value="<?= htmlspecialchars($old['staff_number'] ?? '') ?>" placeholder="NUR-2025-XXX">
                </div>
                <div class="col-md-4">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($old['first_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($old['last_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Department *</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">Select...</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['department_id'] ?>" <?= ($old['department_id']??'')==$d['department_id']?'selected':'' ?>><?= htmlspecialchars($d['department_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Rank</label>
                    <select name="nurse_rank" class="form-select">
                        <?php foreach (['Student','Enrolled','Registered','Senior','Chief'] as $r): ?>
                            <option value="<?= $r ?>" <?= ($old['nurse_rank']??'Registered')===$r?'selected':'' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Availability</label>
                    <select name="availability_status" class="form-select">
                        <?php foreach (['available','busy','off_duty','on_leave'] as $a): ?>
                            <option value="<?= $a ?>" <?= ($old['availability_status']??'available')===$a?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$a)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Max Patient Load</label>
                    <input type="number" name="max_patient_load" class="form-control" min="1" max="20" value="<?= htmlspecialchars($old['max_patient_load'] ?? 6) ?>">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Add Nurse</button>
                <a href="<?= BASE_URL ?>/index.php?page=head_nurse&action=nurses" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
