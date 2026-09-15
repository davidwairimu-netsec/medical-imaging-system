<?php
$pageTitle = 'Edit Patient';
require APP_ROOT . '/views/layouts/main.php';
?>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Patient: <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h5></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form method="POST" novalidate>
            <?= CSRF::field() ?>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Hospital Number *</label><input type="text" name="hospital_number" class="form-control" required value="<?= htmlspecialchars($patient['hospital_number']) ?>"></div>
                <div class="col-md-4"><label class="form-label">National ID</label><input type="text" name="national_id" class="form-control" value="<?= htmlspecialchars($patient['national_id'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Gender *</label>
                    <select name="gender" class="form-select" required>
                        <option value="Male" <?= $patient['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $patient['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= $patient['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">First Name *</label><input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($patient['first_name']) ?>"></div>
                <div class="col-md-4"><label class="form-label">Middle Name</label><input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($patient['middle_name'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Last Name *</label><input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($patient['last_name']) ?>"></div>
                <div class="col-md-4"><label class="form-label">Date of Birth *</label><input type="date" name="date_of_birth" class="form-control" required max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($patient['date_of_birth']) ?>"></div>
                <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($patient['phone'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Emergency Contact</label><input type="text" name="emergency_contact" class="form-control" value="<?= htmlspecialchars($patient['emergency_contact'] ?? '') ?>"></div>
                <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($patient['address'] ?? '') ?></textarea></div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Changes</button>
                <a href="<?= BASE_URL ?>/index.php?page=patients&action=view&id=<?= $patient['patient_id'] ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
