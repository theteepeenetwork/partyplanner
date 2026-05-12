<div class="admin-toolbar d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="admin-page-title mb-0">Event #<?= (int) $event['id'] ?></h1>
    <div class="btn-group">
        <a class="btn btn-outline-secondary" href="<?= site_url('/admin/events') ?>">Back</a>
        <a class="btn btn-outline-primary" href="<?= site_url('/admin/events/' . $event['id'] . '/edit') ?>">Edit</a>
        <a class="btn btn-outline-danger" href="<?= site_url('/admin/events/' . $event['id'] . '/delete') ?>">Delete</a>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm"><div class="card-body small">
            <p><strong>Customer:</strong> <?php if ($customer): ?><a href="<?= site_url('/admin/customers/' . $customer['id']) ?>"><?= esc($customer['name']) ?></a><?php else: ?>—<?php endif; ?></p>
            <p><strong>Vendor:</strong> <?php if ($vendor): ?><a href="<?= site_url('/admin/vendors/' . $vendor['id']) ?>"><?= esc($vendor['name']) ?></a><?php else: ?>—<?php endif; ?></p>
            <p><strong>Date / location:</strong> <?= esc($event['date'] ?? '') ?> — <?= esc($event['location'] ?? '') ?></p>
            <p><strong>Guests:</strong> <?= esc((string) ($event['guest_count'] ?? '')) ?></p>
            <p class="mb-0"><?= nl2br(esc($event['description'] ?? '')) ?></p>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm admin-table-card mb-3">
            <div class="card-header bg-white fw-semibold">Basket lines</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead><tr><th>Service</th><th>Qty</th></tr></thead>
                    <tbody>
                    <?php foreach ($basket as $bi): ?>
                        <tr>
                            <td><?= esc($bi['service_title'] ?? '') ?></td>
                            <td><?= (int) ($bi['quantity'] ?? 1) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($basket)): ?><tr><td colspan="2" class="text-muted">None</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card shadow-sm admin-table-card">
            <div class="card-header bg-white fw-semibold">Bookings</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead><tr><th>ID</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><a href="<?= site_url('/admin/bookings/' . $b['id']) ?>"><?= (int) $b['id'] ?></a></td>
                            <td><?= esc($b['status'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?><tr><td colspan="2" class="text-muted">None</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
