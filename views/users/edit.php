<?php
$pageTitle = 'Edit User';
require APP_ROOT . '/views/layouts/main.php';
?>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="bi bi-pencil"></i> Edit User: <?= htmlspecialchars($user['username']) ?></h5></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form method="POST" novalidate>
            <?= CSRF::field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($user['full_name']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($user['username']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">New Password (leave blank to keep)</label>
                    <input type="password" name="password" class="form-control" minlength="8">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Role *</label>
                    <select name="role_id" class="form-select" required>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['role_id'] ?>" <?= $user['role_id'] == $r['role_id'] ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($r['role_name'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="account_status" class="form-select">
                        <option value="active" <?= $user['account_status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="disabled" <?= $user['account_status'] === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                        <option value="locked" <?= $user['account_status'] === 'locked' ? 'selected' : '' ?>>Locked</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Changes</button>
                <a href="<?= BASE_URL ?>/index.php?page=users" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
