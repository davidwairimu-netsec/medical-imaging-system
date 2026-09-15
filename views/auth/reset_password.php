<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        html, body { height: 100%; margin: 0; }
        .auth-page {
            min-height: 100vh; width: 100%; display: flex; align-items: center;
            justify-content: center; padding: 1rem; box-sizing: border-box;
            background: linear-gradient(135deg, #1a2332 0%, #2a3a52 100%);
        }
        .auth-container { width: 100%; max-width: 460px; }
        .auth-card {
            background: #fff; border-radius: 14px; padding: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .auth-header { text-align: center; margin-bottom: 1.5rem; }
        .auth-logo { font-size: 3rem; color: #0d6efd; }
        .auth-header h2 { font-size: 1.3rem; font-weight: 700; margin-top: 0.5rem; }
    </style>
</head>
<body>
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="bi bi-key-fill auth-logo"></i>
                <h2>Reset Password</h2>
                <?php if ($validToken && $user): ?>
                    <p class="text-muted small mb-0">Resetting password for <strong><?= htmlspecialchars($user['email']) ?></strong></p>
                <?php endif; ?>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($errors['general']) ?>
                </div>
                <div class="text-center">
                    <a href="<?= BASE_URL ?>/index.php?page=auth&action=forgot" class="btn btn-primary">
                        <i class="bi bi-arrow-repeat"></i> Request New Link
                    </a>
                </div>
            <?php elseif ($validToken): ?>
                <form method="POST" novalidate id="resetForm">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="new_password" id="new_password"
                                   class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>"
                                   placeholder="Enter new password" required minlength="8" autofocus>
                            <button class="btn btn-outline-secondary" type="button" id="toggleNew">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">At least 8 characters, 1 uppercase letter, 1 number.</div>
                        <?php if (isset($errors['new_password'])): ?>
                            <div class="text-danger small"><?= htmlspecialchars($errors['new_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="confirm_password" id="confirm_password"
                                   class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                                   placeholder="Re-enter new password" required minlength="8">
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="text-danger small"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle"></i> Reset Password
                    </button>
                </form>
            <?php else: ?>
                <div class="text-center">
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
['toggleNew'].forEach(function(id) {
    const btn = document.getElementById(id);
    if (!btn) return;
    btn.addEventListener('click', function() {
        const pwd = this.parentElement.querySelector('input');
        const icon = this.querySelector('i');
        if (pwd.type === 'password') { pwd.type = 'text'; icon.classList.replace('bi-eye', 'bi-eye-slash'); }
        else { pwd.type = 'password'; icon.classList.replace('bi-eye-slash', 'bi-eye'); }
    });
});
</script>
</body>
</html>
