<div class="admin-toolbar d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="admin-page-title mb-0">Booking #<?= (int) $booking['id'] ?></h1>
    <div class="btn-group">
        <a class="btn btn-outline-secondary" href="<?= site_url('/admin/bookings') ?>">Back</a>
        <a class="btn btn-outline-danger" href="<?= site_url('/admin/bookings/' . $booking['id'] . '/delete') ?>">Delete</a>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card shadow-sm"><div class="card-body small">
            <h2 class="h6">Status</h2>
            <form method="post" action="<?= site_url('/admin/bookings/' . $booking['id'] . '/status') ?>" class="d-flex flex-wrap align-items-center">
                <?= csrf_field() ?>
                <select name="status" class="form-select form-select-sm me-2 mb-2 mb-sm-0" style="min-width:9rem;">
                    <?php foreach (['pending','accepted','confirmed','declined','cancelled','completed'] as $st): ?>
                        <option value="<?= esc($st) ?>" <?= ($booking['status'] ?? '') === $st ? 'selected' : '' ?>><?= esc($st) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-primary mb-2 mb-sm-0" type="submit">Update</button>
            </form>
            <p class="mt-2 mb-0 text-muted">Payment intent: <?= esc($booking['payment_intent_id'] ?? '—') ?></p>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm"><div class="card-body small">
            <h2 class="h6">Customer</h2>
            <?php if ($customer): ?>
                <p class="mb-0"><a href="<?= site_url('/admin/customers/' . $customer['id']) ?>"><?= esc($customer['name']) ?></a><br><?= esc($customer['email']) ?></p>
            <?php else: ?><p class="text-muted mb-0">—</p><?php endif; ?>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm"><div class="card-body small">
            <h2 class="h6">Event</h2>
            <?php if ($event): ?>
                <p class="mb-0"><a href="<?= site_url('/admin/events/' . $event['id']) ?>"><?= esc($event['title']) ?></a><br><?= esc($event['date'] ?? '') ?></p>
            <?php else: ?><p class="text-muted mb-0">—</p><?php endif; ?>
        </div></div>
    </div>
</div>
<div class="card shadow-sm admin-table-card mb-3">
    <div class="card-header bg-white fw-semibold">Line items</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead><tr><th>Service</th><th>Vendor</th><th>Qty</th><th>Item status</th><th>Times</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= esc($it['service_title'] ?? '') ?></td>
                    <td><?= esc($it['vendor_name'] ?? '') ?></td>
                    <td><?= (int) ($it['quantity'] ?? 1) ?></td>
                    <td><?= esc($it['status'] ?? '') ?></td>
                    <td><?= esc(($it['start_time'] ?? '') . ' – ' . ($it['end_time'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="card shadow-sm admin-table-card mb-3">
    <div class="card-header bg-white fw-semibold">Payments</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead><tr><th>ID</th><th>Status</th><th>Amount</th><th>Currency</th><th>Type</th><th>When</th></tr></thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= (int) $p['id'] ?></td>
                    <td><?= esc($p['payment_status'] ?? '') ?></td>
                    <td><?= esc((string) ($p['amount_paid'] ?? '')) ?></td>
                    <td><?= esc($p['currency'] ?? '') ?></td>
                    <td><?= esc($p['payment_type'] ?? '') ?></td>
                    <td><?= esc($p['created_at'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($payments)): ?><tr><td colspan="6" class="text-muted">No payment rows</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if (!empty($chatRooms)): ?>
<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Related chats</div>
    <div class="card-body small">
        <?php foreach ($chatRooms as $cr): ?>
            <a href="<?= site_url('/admin/messages/' . $cr['id']) ?>">Room #<?= (int) $cr['id'] ?></a><br>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
