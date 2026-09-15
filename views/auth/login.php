<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/public/css/style.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        .login-page {
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a2332 0%, #2a3a52 100%);
            padding: 1rem;
            box-sizing: border-box;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        .login-card {
            background: #fff;
            border-radius: 14px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-logo {
            font-size: 3rem;
            color: #0d6efd;
        }
        .login-header h2 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-top: 0.5rem;
        }
        .login-footer {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #f0f2f5;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <i class="bi bi-heart-pulse-fill login-logo"></i>
                    <h2><?= APP_NAME ?></h2>
                    <p class="text-muted">Digital Medical Imaging Management System</p>
                </div>

                <?php if (!empty($timeoutMessage)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-clock-history"></i> <?= htmlspecialchars($timeoutMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if ($logoutMessage = Session::flash('_logout_message')): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> <?= htmlspecialchars($logoutMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/login.php<?= !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" novalidate>
                    <?= CSRF::field() ?>
                    <?php if (!empty($_GET['redirect'])): ?>
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username or Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username" name="username"
                                   placeholder="Enter your username" required autofocus
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Enter your password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="text-end mb-3">
                        <a href="<?= BASE_URL ?>/index.php?page=auth&action=forgot" class="small text-decoration-none">
                            <i class="bi bi-question-circle"></i> Forgot password?
                        </a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Sign In
                    </button>
                </form>

                <div class="login-footer">
                    <p class="text-muted small mb-0">
                        <i class="bi bi-shield-lock"></i>
                        Authorised access only. All activity is monitored and logged.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    </script>
</body>
</html>
