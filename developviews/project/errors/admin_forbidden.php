<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
    <div class="container text-center py-5">
        <h1 class="display-5">403 — Forbidden</h1>
        <p class="lead text-muted">You do not have permission to access the admin area.</p>
        <a href="<?= esc(base_url('/')) ?>" class="btn btn-primary">Back to site</a>
    </div>
</body>
</html>
