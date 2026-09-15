<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-key-fill"></i> Change Password</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <?= CSRF::field() ?>

                    <div class="mb-3">
                        <label class="form-label">Current Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="current_password" id="current_password"
                                   class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>"
                                   required autofocus>
                            <button class="btn btn-outline-secondary toggle-pwd" data-target="current_password" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['current_password'])): ?>
                            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['current_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">New Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="new_password" id="new_password"
                                   class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>"
                                   required minlength="8">
                            <button class="btn btn-outline-secondary toggle-pwd" data-target="new_password" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">At least 8 characters, 1 uppercase letter, 1 number.</div>
                        <?php if (isset($errors['new_password'])): ?>
                            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['new_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm New Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="confirm_password" id="confirm_password"
                                   class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                                   required minlength="8">
                            <button class="btn btn-outline-secondary toggle-pwd" data-target="confirm_password" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Change Password
                        </button>
                        <a href="<?= BASE_URL ?>/index.php?page=dashboard" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="alert alert-info small mt-3">
            <i class="bi bi-shield-check"></i>
            <strong>Security tip:</strong> After changing your password, all future logins will require the new password. Make sure you remember it or store it in a password manager.
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-pwd').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const targetId = this.dataset.target;
        const pwd = document.getElementById(targetId);
        const icon = this.querySelector('i');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            pwd.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
});
</script>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
