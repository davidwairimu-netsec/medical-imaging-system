<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 - Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="text-center">
        <i class="bi bi-shield-lock-fill text-danger" style="font-size: 5rem;"></i>
        <h1 class="display-4">403</h1>
        <h3>Access Denied</h3>
        <p class="text-muted">You do not have permission to access this resource.</p>
        <a href="<?= BASE_URL ?>/index.php?page=dashboard" class="btn btn-primary">
            <i class="bi bi-house"></i> Return to Dashboard
        </a>
    </div>
</div>
</body>
</html>