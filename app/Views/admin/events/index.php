<?php
$filtersActive = !empty($customer_id ?? null) || !empty($vendor_id ?? null)
    || (($status ?? '') !== '' && $status !== null)
    || (($event_type ?? '') !== '' && $event_type !== null)
    || (($location ?? '') !== '' && $location !== null)
    || (($date_from ?? '') !== '' && $date_from !== null)
    || (($date_to ?? '') !== '' && $date_to !== null);
?>
<header class="admin-page-header">
    <h1 class="admin-page-title">Events</h1>
    <p class="admin-page-subtitle">Customer events and planning details. Use filters to narrow by party, host, or schedule.</p>
</header>
<form class="row g-2 admin-filters small" method="get">
    <div class="col-md-2"><input class="form-control" name="customer_id" placeholder="Customer ID" value="<?= $customer_id ? (int) $customer_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="vendor_id" placeholder="Vendor ID" value="<?= $vendor_id ? (int) $vendor_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="status" placeholder="Status" value="<?= esc($status) ?>"></div>
    <div class="col-md-2"><input class="form-control" name="event_type" placeholder="Event type" value="<?= esc($event_type) ?>"></div>
    <div class="col-md-2"><input class="form-control" name="location" placeholder="Location" value="<?= esc($location) ?>"></div>
    <div class="col-md-2"><input class="form-control" type="date" name="date_from" value="<?= esc($date_from) ?>"></div>
    <div class="col-md-2"><input class="form-control" type="date" name="date_to" value="<?= esc($date_to) ?>"></div>
    <div class="col-12 d-flex flex-wrap">
        <button class="btn btn-primary btn-sm me-2 mb-2 mb-sm-0" type="submit">Filter</button>
        <a class="btn btn-outline-secondary btn-sm mb-2 mb-sm-0" href="<?= site_url('/admin/events') ?>">Reset</a>
    </div>
</form>
<?php if (empty($events)): ?>
    <div class="card shadow-sm">
        <div class="admin-empty">
            <div class="admin-empty-icon"><i class="fas fa-champagne-glasses" aria-hidden="true"></i></div>
            <p class="admin-empty-title"><?= $filtersActive ? 'No events match these filters' : 'No events yet' ?></p>
            <p class="admin-empty-text">
                <?= $filtersActive
                    ? 'Try clearing one or more filters to broaden the results.'
                    : 'Events are created when customers plan a celebration on the site.' ?>
            </p>
            <div class="admin-empty-actions">
                <?php if ($filtersActive): ?>
                    <a class="btn btn-primary" href="<?= site_url('/admin/events') ?>">Clear filters</a>
                <?php endif; ?>
                <a class="btn btn-outline-secondary<?= $filtersActive ? ' ms-2' : '' ?>" href="<?= site_url('/admin/customers') ?>">View customers</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm admin-table-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle small">
                <thead><tr><th>ID</th><th>Title</th><th>Customer</th><th>Date</th><th>Type</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($events as $e): ?>
                    <tr>
                        <td class="text-muted"><?= (int) $e['id'] ?></td>
                        <td><?= esc($e['title']) ?></td>
                        <td><?= esc($e['customer_name'] ?? '—') ?></td>
                        <td class="text-nowrap"><?= esc($e['date'] ?? '') ?></td>
                        <td><?= esc($e['event_type'] ?? '') ?></td>
                        <td><?= esc($e['status'] ?? '') ?></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/events/' . $e['id']) ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
