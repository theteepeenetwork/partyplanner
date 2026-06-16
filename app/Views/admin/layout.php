<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin') ?> — Partysmith</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/partysmith-app.css') ?>">
    <style>
        body { background: #f4f6f9; min-height: 100vh; }
        .admin-sidebar { min-height: 100vh; background: #1e2a3a; color: #e9ecef; }
        .admin-sidebar a { color: #cbd5e1; text-decoration: none; display: block; padding: .55rem 1rem; border-radius: .35rem; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: rgba(255,255,255,.08); color: #fff; }
        .admin-sidebar .brand { font-weight: 700; letter-spacing: .02em; }
        .admin-main { padding: 1.5rem 1.75rem 2rem; max-width: 100%; }
        .admin-main .alert:last-of-type { margin-bottom: 1.25rem; }
        .admin-page-header { margin-bottom: 1.5rem; }
        .admin-page-title {
            font-size: 1.375rem;
            font-weight: 600;
            line-height: 1.3;
            color: #212529;
            margin: 0 0 .25rem;
            letter-spacing: -.02em;
        }
        .admin-page-subtitle {
            color: #6c757d;
            font-size: .9375rem;
            line-height: 1.5;
            margin: 0;
            max-width: 42rem;
        }
        .admin-toolbar { margin-bottom: 1.5rem; }
        .admin-toolbar .admin-page-title { margin-bottom: 0; }
        .admin-filters { margin-bottom: 1.25rem; }
        .admin-table-card .table { margin-bottom: 0; }
        .admin-table-card .table thead th {
            background: #f1f3f5;
            border-bottom: 2px solid #dee2e6;
            font-size: .6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .055em;
            color: #6c757d;
            padding: .65rem .75rem;
            vertical-align: middle;
            white-space: nowrap;
        }
        .admin-table-card .table tbody td {
            vertical-align: middle;
            padding: .65rem .75rem;
            border-bottom-color: #e9ecef;
        }
        .admin-table-card .table.table-sm thead th,
        .admin-table-card .table.table-sm tbody td {
            padding: .5rem .65rem;
        }
        .admin-table-card .table tbody tr:hover { background: rgba(13, 110, 253, .04); }
        .admin-table-card .card-body:empty { display: none; }
        .admin-empty {
            padding: 2.75rem 1.5rem;
            text-align: center;
        }
        .admin-empty-icon {
            font-size: 2.5rem;
            color: #adb5bd;
            line-height: 1;
            margin-bottom: 1rem;
        }
        .admin-empty-title {
            font-size: 1.0625rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: .5rem;
        }
        .admin-empty-text {
            color: #6c757d;
            font-size: .9375rem;
            max-width: 28rem;
            margin: 0 auto;
        }
        .admin-empty-actions { margin-top: 1.25rem; }
        .admin-danger-confirm {
            border: 1px solid #f5c2c7;
            border-radius: .375rem;
            background: #fff;
            overflow: hidden;
            max-width: 44rem;
        }
        .admin-danger-confirm__header {
            background: #f8d7da;
            color: #842029;
            padding: 1rem 1.25rem;
            font-weight: 600;
            font-size: 1.125rem;
        }
        .admin-danger-confirm__body { padding: 1.25rem; }
        .admin-danger-confirm__back { font-size: .875rem; margin-bottom: 1rem; }
        .admin-danger-confirm__back a { text-decoration: none; }
        .admin-danger-confirm__back a:hover { text-decoration: underline; }
        .admin-danger-summary {
            background: #f8f9fa;
            border-radius: .35rem;
            padding: 1rem 1.15rem;
            margin-bottom: 1.25rem;
            font-size: .9375rem;
        }
        .admin-danger-summary ul { margin-bottom: 0; padding-left: 1.15rem; }
        .admin-danger-summary .label {
            font-size: .6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .055em;
            color: #6c757d;
            margin-bottom: .2rem;
        }
        .admin-danger-footnote { font-size: .875rem; color: #6c757d; margin-bottom: 1rem; }
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
            <a class="<?= ($activeNav ?? '') === 'reviews' ? 'active' : '' ?>" href="<?= site_url('/admin/reviews') ?>"><i class="fas fa-star me-2"></i>Reviews</a>
            <hr class="border-secondary">
            <a href="<?= site_url('/') ?>"><i class="fas fa-house me-2"></i>Site home</a>
            <a href="<?= site_url('/logout') ?>"><i class="fas fa-right-from-bracket me-2"></i>Logout</a>
        </nav>
        <div class="col-md-9 col-lg-10 admin-main ps-app">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= esc(session()->getFlashdata('success')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= esc(session()->getFlashdata('error')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?= $inner ?? '' ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/partysmith.js') ?>" defer></script>
</body>
</html>
