<h1 class="h3 mb-3">Events</h1>
<form class="row g-2 mb-3 small" method="get">
    <div class="col-md-2"><input class="form-control" name="customer_id" placeholder="Customer ID" value="<?= $customer_id ? (int) $customer_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="vendor_id" placeholder="Vendor ID" value="<?= $vendor_id ? (int) $vendor_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="status" placeholder="Status" value="<?= esc($status) ?>"></div>
    <div class="col-md-2"><input class="form-control" name="event_type" placeholder="Event type" value="<?= esc($event_type) ?>"></div>
    <div class="col-md-2"><input class="form-control" name="location" placeholder="Location" value="<?= esc($location) ?>"></div>
    <div class="col-md-2"><input class="form-control" type="date" name="date_from" value="<?= esc($date_from) ?>"></div>
    <div class="col-md-2"><input class="form-control" type="date" name="date_to" value="<?= esc($date_to) ?>"></div>
    <div class="col-12"><button class="btn btn-primary btn-sm" type="submit">Filter</button>
        <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('/admin/events') ?>">Reset</a></div>
</form>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead><tr><th>ID</th><th>Title</th><th>Customer</th><th>Date</th><th>Type</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($events as $e): ?>
                <tr>
                    <td><?= (int) $e['id'] ?></td>
                    <td><?= esc($e['title']) ?></td>
                    <td><?= esc($e['customer_name'] ?? '—') ?></td>
                    <td><?= esc($e['date'] ?? '') ?></td>
                    <td><?= esc($e['event_type'] ?? '') ?></td>
                    <td><?= esc($e['status'] ?? '') ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/events/' . $e['id']) ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($events)): ?><div class="card-body text-muted">No events match.</div><?php endif; ?>
</div>
