<?php
$filtersActive = !empty($vendor_id ?? null) || !empty($service_id ?? null) || !empty($flagged ?? false);
?>
<header class="admin-page-header">
    <h1 class="admin-page-title">Reviews</h1>
    <p class="admin-page-subtitle">Customer reviews across all services. Filter by vendor, service, or flagged (profanity-masked) reviews.</p>
</header>
<form class="row g-2 admin-filters small align-items-end" method="get">
    <div class="col-md-2">
        <label class="form-label mb-0">Vendor ID</label>
        <input class="form-control" name="vendor_id" value="<?= $vendor_id ? (int) $vendor_id : '' ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label mb-0">Service ID</label>
        <input class="form-control" name="service_id" value="<?= $service_id ? (int) $service_id : '' ?>">
    </div>
    <div class="col-md-2 form-check mt-4">
        <input class="form-check-input" type="checkbox" name="flagged" value="1" id="flg" <?= $flagged ? 'checked' : '' ?>>
        <label class="form-check-label" for="flg">Flagged only</label>
    </div>
    <div class="col-auto d-flex flex-wrap">
        <button class="btn btn-primary me-2 mb-2 mb-md-0" type="submit">Filter</button>
        <a class="btn btn-outline-secondary mb-2 mb-md-0" href="<?= site_url('/admin/reviews') ?>">Reset</a>
    </div>
</form>
<?php if (empty($reviews)): ?>
    <div class="card shadow-sm">
        <div class="admin-empty">
            <div class="admin-empty-icon"><i class="fas fa-star" aria-hidden="true"></i></div>
            <p class="admin-empty-title"><?= $filtersActive ? 'No reviews match these filters' : 'No reviews yet' ?></p>
            <p class="admin-empty-text">
                <?= $filtersActive
                    ? 'Adjust the filters above.'
                    : 'Reviews appear once customers review completed bookings.' ?>
            </p>
            <?php if ($filtersActive): ?>
                <div class="admin-empty-actions">
                    <a class="btn btn-primary" href="<?= site_url('/admin/reviews') ?>">Clear filters</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm admin-table-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle small">
                <thead><tr><th>ID</th><th>Rating</th><th>Title</th><th>Customer</th><th>Service</th><th>Vendor</th><th>Flagged</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($reviews as $r): ?>
                    <tr<?= !empty($r['flagged']) ? ' class="table-warning"' : '' ?>>
                        <td class="text-muted"><?= (int) $r['id'] ?></td>
                        <td><?= str_repeat('★', (int) $r['rating']) ?><span class="text-muted"><?= str_repeat('☆', 5 - (int) $r['rating']) ?></span></td>
                        <td><?= esc($r['title']) ?></td>
                        <td><?= esc($r['customer_name'] ?? '—') ?></td>
                        <td><?= esc($r['service_title'] ?? '—') ?></td>
                        <td><?= esc($r['vendor_name'] ?? '—') ?></td>
                        <td><?= !empty($r['flagged']) ? '<span class="badge bg-warning text-dark">flagged</span>' : '—' ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/reviews/' . $r['id']) ?>">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body border-top"><?= $pager->links() ?></div>
    </div>
<?php endif; ?>
