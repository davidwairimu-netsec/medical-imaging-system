<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | <?= APP_NAME ?></title>
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
                <i class="bi bi-shield-lock-fill auth-logo"></i>
                <h2>Forgot Password</h2>
                <p class="text-muted small mb-0">Enter your email and we'll generate a reset link.</p>
            </div>

            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?= htmlspecialchars($successMessage) ?>
                </div>
                <?php if (!empty($resetLink)): ?>
                    <div class="alert alert-warning">
                        <strong><i class="bi bi-info-circle"></i> Prototype Mode:</strong>
                        <p class="small mb-2">In production, this link would be emailed. For this academic prototype, use the link below:</p>
                        <a href="<?= htmlspecialchars($resetLink) ?>" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-key"></i> Open Reset Link
                        </a>
                        <p class="small text-muted mt-2 mb-0"><strong>Note:</strong> The link expires in 1 hour.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle"></i> If that email is registered, a reset link has been generated. Check above.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <?= CSRF::field() ?>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                               placeholder="your.email@hospital.demo" required autofocus
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-send"></i> Generate Reset Link
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>/login.php" class="text-decoration-none small">
                    <i class="bi bi-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
