<?php
$filtersActive = !empty($customer_id ?? null) || !empty($vendor_id ?? null) || !empty($service_id ?? null)
    || (($status ?? '') !== '' && $status !== null)
    || (($date_from ?? '') !== '' && $date_from !== null)
    || (($date_to ?? '') !== '' && $date_to !== null);
?>
<header class="admin-page-header">
    <h1 class="admin-page-title">Bookings</h1>
    <p class="admin-page-subtitle">Reservations between customers and vendors. Filter by IDs, status, or date range.</p>
</header>
<form class="row g-2 admin-filters small" method="get">
    <div class="col-md-2"><input class="form-control" name="customer_id" placeholder="Customer ID" value="<?= $customer_id ? (int) $customer_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="vendor_id" placeholder="Vendor ID" value="<?= $vendor_id ? (int) $vendor_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="service_id" placeholder="Service ID" value="<?= $service_id ? (int) $service_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="status" placeholder="Status" value="<?= esc($status) ?>"></div>
    <div class="col-md-2"><input class="form-control" type="date" name="date_from" value="<?= esc($date_from) ?>"></div>
    <div class="col-md-2"><input class="form-control" type="date" name="date_to" value="<?= esc($date_to) ?>"></div>
    <div class="col-12 d-flex flex-wrap">
        <button class="btn btn-primary btn-sm me-2 mb-2 mb-sm-0" type="submit">Filter</button>
        <a class="btn btn-outline-secondary btn-sm mb-2 mb-sm-0" href="<?= site_url('/admin/bookings') ?>">Reset</a>
    </div>
</form>
<?php if (empty($bookings)): ?>
    <div class="card shadow-sm">
        <div class="admin-empty">
            <div class="admin-empty-icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></div>
            <p class="admin-empty-title"><?= $filtersActive ? 'No bookings match these filters' : 'No bookings yet' ?></p>
            <p class="admin-empty-text">
                <?= $filtersActive
                    ? 'Widen the date range or clear filters to see more results.'
                    : 'Bookings show up when customers complete a reservation flow.' ?>
            </p>
            <div class="admin-empty-actions">
                <?php if ($filtersActive): ?>
                    <a class="btn btn-primary" href="<?= site_url('/admin/bookings') ?>">Clear filters</a>
                <?php endif; ?>
                <a class="btn btn-outline-secondary<?= $filtersActive ? ' ms-2' : '' ?>" href="<?= site_url('/admin/events') ?>">View events</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm admin-table-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><th>ID</th><th>Customer</th><th>Event</th><th>Event date</th><th>Status</th><th>Created</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td class="text-muted"><?= (int) $b['id'] ?></td>
                        <td><?= esc($b['customer_name'] ?? '') ?></td>
                        <td><?= esc($b['event_title'] ?? '—') ?></td>
                        <td class="text-nowrap"><?= esc($b['event_date'] ?? '') ?></td>
                        <td><?= esc($b['status'] ?? '') ?></td>
                        <td class="text-nowrap small"><?= esc($b['created_at'] ?? '') ?></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/bookings/' . $b['id']) ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body border-top"><?= $pager->links() ?></div>
    </div>
<?php endif; ?>
