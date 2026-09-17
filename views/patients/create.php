<?php
$pageTitle = 'Register New Patient';
require APP_ROOT . '/views/layouts/main.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-person-plus"></i> Patient Registration</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($errors['duplicate_records'])): ?>
            <?php $severity = $errors['duplicate_severity'] ?? 'possible'; ?>
            <div class="alert alert-<?= $severity === 'exact' ? 'danger' : 'warning' ?>">
                <h6 class="mb-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong><?= $severity === 'exact' ? 'Duplicate Detected' : 'Possible Duplicate Detected' ?></strong>
                </h6>
                <p class="mb-2"><?= htmlspecialchars($errors['duplicate']) ?></p>
                <hr class="my-2">
                <div class="small">
                    <strong>Matching records found:</strong>
                    <ul class="mb-0 mt-1">
                        <?php foreach ($errors['duplicate_records'] as $key => $match): ?>
                            <li>
                                <span class="badge bg-<?= $match['confidence'] === 'exact' ? 'danger' : ($match['confidence'] === 'high' ? 'warning' : 'secondary') ?>">
                                    <?= htmlspecialchars($match['confidence']) ?>
                                </span>
                                <strong><?= htmlspecialchars($match['record']['first_name'] . ' ' . $match['record']['last_name']) ?></strong>
                                (Hosp: <?= htmlspecialchars($match['record']['hospital_number']) ?>)
                                — <?= htmlspecialchars($match['message']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <p class="mt-2 mb-0 small text-muted">
                    <i class="bi bi-info-circle"></i>
                    If this is genuinely a different patient, you may proceed. The attempt will be logged for audit.
                </p>
            </div>
        <?php elseif (!empty($errors['duplicate'])): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($errors['duplicate']) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" novalidate>
            <?= CSRF::field() ?>
            
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Hospital Number <span class="text-danger">*</span></label>
                    <input type="text" name="hospital_number" class="form-control <?= isset($errors['hospital_number']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($old['hospital_number'] ?? '') ?>" required>
                    <?php if (isset($errors['hospital_number'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['hospital_number']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">National ID</label>
                    <input type="text" name="national_id" class="form-control"
                           value="<?= htmlspecialchars($old['national_id'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select <?= isset($errors['gender']) ? 'is-invalid' : '' ?>" required>
                        <option value="">Select...</option>
                        <option value="Male" <?= ($old['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($old['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= ($old['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                    <?php if (isset($errors['gender'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['gender']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required>
                    <?php if (isset($errors['first_name'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" class="form-control"
                           value="<?= htmlspecialchars($old['middle_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required>
                    <?php if (isset($errors['last_name'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_birth" class="form-control <?= isset($errors['date_of_birth']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($old['date_of_birth'] ?? '') ?>" required max="<?= date('Y-m-d') ?>">
                    <?php if (isset($errors['date_of_birth'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['date_of_birth']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Emergency Contact</label>
                    <input type="text" name="emergency_contact" class="form-control"
                           value="<?= htmlspecialchars($old['emergency_contact'] ?? '') ?>">
                </div>
                
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($old['address'] ?? '') ?></textarea>
                </div>
            </div>
            
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Register Patient
                </button>
                <a href="<?= BASE_URL ?>/index.php?page=patients" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>