<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>500 - Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="text-center">
        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 5rem;"></i>
        <h1 class="display-4">500</h1>
        <h3>System Error</h3>
        <p class="text-muted">An unexpected error occurred. Please try again later.</p>
        <a href="<?= BASE_URL ?>/index.php?page=dashboard" class="btn btn-primary">
            <i class="bi bi-house"></i> Return to Dashboard
        </a>
    </div>
</div>
</body>
</html>