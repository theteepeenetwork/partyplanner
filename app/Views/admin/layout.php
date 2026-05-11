<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin') ?> — For Your Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        body { background: #f4f6f9; min-height: 100vh; }
        .admin-sidebar { min-height: 100vh; background: #1e2a3a; color: #e9ecef; }
        .admin-sidebar a { color: #cbd5e1; text-decoration: none; display: block; padding: .55rem 1rem; border-radius: .35rem; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: rgba(255,255,255,.08); color: #fff; }
        .admin-sidebar .brand { font-weight: 700; letter-spacing: .02em; }
        .admin-main { padding: 1.5rem; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row g-0">
        <nav class="col-md-3 col-lg-2 admin-sidebar p-3">
            <div class="brand mb-3">Admin</div>
            <a class="<?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= site_url('/admin') ?>"><i class="fas fa-gauge me-2"></i>Dashboard</a>
            <a class="<?= ($activeNav ?? '') === 'customers' ? 'active' : '' ?>" href="<?= site_url('/admin/customers') ?>"><i class="fas fa-users me-2"></i>Customers</a>
            <a class="<?= ($activeNav ?? '') === 'vendors' ? 'active' : '' ?>" href="<?= site_url('/admin/vendors') ?>"><i class="fas fa-store me-2"></i>Vendors</a>
            <a class="<?= ($activeNav ?? '') === 'bookings' ? 'active' : '' ?>" href="<?= site_url('/admin/bookings') ?>"><i class="fas fa-calendar-check me-2"></i>Bookings</a>
            <a class="<?= ($activeNav ?? '') === 'messages' ? 'active' : '' ?>" href="<?= site_url('/admin/messages') ?>"><i class="fas fa-comments me-2"></i>Messages</a>
            <a class="<?= ($activeNav ?? '') === 'services' ? 'active' : '' ?>" href="<?= site_url('/admin/services') ?>"><i class="fas fa-briefcase me-2"></i>Services</a>
            <a class="<?= ($activeNav ?? '') === 'events' ? 'active' : '' ?>" href="<?= site_url('/admin/events') ?>"><i class="fas fa-champagne-glasses me-2"></i>Events</a>
            <a class="<?= ($activeNav ?? '') === 'pages' ? 'active' : '' ?>" href="<?= site_url('/admin/pages') ?>"><i class="fas fa-file-lines me-2"></i>Pages</a>
            <hr class="border-secondary">
            <a href="<?= site_url('/') ?>"><i class="fas fa-house me-2"></i>Site home</a>
            <a href="<?= site_url('/logout') ?>"><i class="fas fa-right-from-bracket me-2"></i>Logout</a>
        </nav>
        <div class="col-md-9 col-lg-10 admin-main">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?= $inner ?? '' ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
