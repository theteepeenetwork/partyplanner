<?php
$filtersActive = !empty($vendor_id ?? null) || !empty($category_id ?? null)
    || (($status ?? '') !== '' && $status !== null)
    || !empty($show_deleted ?? false);
?>
<header class="admin-page-header">
    <h1 class="admin-page-title">Services</h1>
    <p class="admin-page-subtitle">Listings offered by vendors. Filter by vendor, category, status, or include soft-deleted rows.</p>
</header>
<form class="row g-2 admin-filters small align-items-end" method="get">
    <div class="col-md-2">
        <label class="form-label mb-0">Vendor ID</label>
        <input class="form-control" name="vendor_id" value="<?= $vendor_id ? (int) $vendor_id : '' ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label mb-0">Category ID</label>
        <input class="form-control" name="category_id" value="<?= $category_id ? (int) $category_id : '' ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label mb-0">Status</label>
        <input class="form-control" name="status" value="<?= esc($status) ?>" placeholder="active / inactive">
    </div>
    <div class="col-md-2 form-check mt-4">
        <input class="form-check-input" type="checkbox" name="deleted" value="1" id="del" <?= $show_deleted ? 'checked' : '' ?>>
        <label class="form-check-label" for="del">Show soft-deleted</label>
    </div>
    <div class="col-auto d-flex flex-wrap">
        <button class="btn btn-primary me-2 mb-2 mb-md-0" type="submit">Filter</button>
        <a class="btn btn-outline-secondary mb-2 mb-md-0" href="<?= site_url('/admin/services') ?>">Reset</a>
    </div>
</form>
<?php if (empty($services)): ?>
    <div class="card shadow-sm">
        <div class="admin-empty">
            <div class="admin-empty-icon"><i class="fas fa-briefcase" aria-hidden="true"></i></div>
            <p class="admin-empty-title"><?= $filtersActive ? 'No services match these filters' : 'No services yet' ?></p>
            <p class="admin-empty-text">
                <?= $filtersActive
                    ? 'Adjust filters or include soft-deleted services if you expect to see archived listings.'
                    : 'Services are created by vendors from their dashboards.' ?>
            </p>
            <div class="admin-empty-actions">
                <?php if ($filtersActive): ?>
                    <a class="btn btn-primary" href="<?= site_url('/admin/services') ?>">Clear filters</a>
                <?php endif; ?>
                <a class="btn btn-outline-secondary<?= $filtersActive ? ' ms-2' : '' ?>" href="<?= site_url('/admin/vendors') ?>">View vendors</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm admin-table-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle small">
                <thead><tr><th>ID</th><th>Title</th><th>Vendor</th><th>Category</th><th>Status</th><th>Deleted</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($services as $s): ?>
                    <tr>
                        <td class="text-muted"><?= (int) $s['id'] ?></td>
                        <td><?= esc($s['title']) ?></td>
                        <td><?= esc($s['vendor_name'] ?? '') ?></td>
                        <td><?= esc($s['category_name'] ?? '—') ?></td>
                        <td><?= esc($s['status'] ?? '') ?></td>
                        <td><?= !empty($s['deleted_at']) ? esc($s['deleted_at']) : '—' ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/services/' . $s['id']) ?>">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body border-top"><?= $pager->links() ?></div>
    </div>
<?php endif; ?>
