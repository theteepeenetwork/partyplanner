<h1 class="h3 mb-3">Bookings</h1>
<form class="row g-2 mb-3 small" method="get">
    <div class="col-md-2"><input class="form-control" name="customer_id" placeholder="Customer ID" value="<?= $customer_id ? (int) $customer_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="vendor_id" placeholder="Vendor ID" value="<?= $vendor_id ? (int) $vendor_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="service_id" placeholder="Service ID" value="<?= $service_id ? (int) $service_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="status" placeholder="Status" value="<?= esc($status) ?>"></div>
    <div class="col-md-2"><input class="form-control" type="date" name="date_from" value="<?= esc($date_from) ?>"></div>
    <div class="col-md-2"><input class="form-control" type="date" name="date_to" value="<?= esc($date_to) ?>"></div>
    <div class="col-12"><button class="btn btn-primary btn-sm" type="submit">Filter</button>
        <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('/admin/bookings') ?>">Reset</a></div>
</form>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>ID</th><th>Customer</th><th>Event</th><th>Event date</th><th>Status</th><th>Created</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= (int) $b['id'] ?></td>
                    <td><?= esc($b['customer_name'] ?? '') ?></td>
                    <td><?= esc($b['event_title'] ?? '—') ?></td>
                    <td><?= esc($b['event_date'] ?? '') ?></td>
                    <td><?= esc($b['status'] ?? '') ?></td>
                    <td><?= esc($b['created_at'] ?? '') ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/bookings/' . $b['id']) ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-body"><?= $pager->links() ?></div>
</div>
