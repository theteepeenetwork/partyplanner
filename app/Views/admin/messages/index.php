<h1 class="h3 mb-3">Messages
    <?php if (($moderation ?? '') === 'pending'): ?>
        <span class="badge bg-warning text-dark ms-2">Language review queue</span>
    <?php endif; ?>
</h1>
<form class="row g-2 mb-3 small" method="get">
    <div class="col-md-3"><input class="form-control" name="q" placeholder="Keyword in message" value="<?= esc($q) ?>"></div>
    <div class="col-md-2"><input class="form-control" name="user_id" placeholder="User ID" value="<?= $user_id ? (int) $user_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="vendor_id" placeholder="Vendor ID" value="<?= $vendor_id ? (int) $vendor_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="customer_id" placeholder="Customer ID" value="<?= $customer_id ? (int) $customer_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="booking_id" placeholder="Booking ID" value="<?= $booking_id ? (int) $booking_id : '' ?>"></div>
    <?php if (($moderation ?? '') === 'pending'): ?>
        <input type="hidden" name="moderation" value="pending">
    <?php endif; ?>
    <div class="col-auto"><button class="btn btn-primary" type="submit">Filter</button></div>
    <div class="col-auto"><a class="btn btn-outline-secondary" href="<?= site_url('/admin/messages') ?>">Reset</a></div>
    <div class="col-12 mt-1">
        <a class="btn btn-sm btn-outline-danger" href="<?= site_url('/admin/messages?moderation=pending') ?>">Pending language review only</a>
    </div>
</form>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Room</th><th>Vendor</th><th>Customer</th><th>Service</th><th>Flagged</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rooms as $r): ?>
                <tr>
                    <td>#<?= (int) $r['id'] ?></td>
                    <td><?= esc($r['vendor_name'] ?? '') ?> <span class="text-muted">(#<?= (int) ($r['vendor_id'] ?? 0) ?>)</span></td>
                    <td><?= esc($r['customer_name'] ?? '') ?> <span class="text-muted">(#<?= (int) ($r['customer_id'] ?? 0) ?>)</span></td>
                    <td><?= esc($r['service_title'] ?? '—') ?></td>
                    <td><?= !empty($r['flagged_for_review']) ? '<span class="badge bg-danger">yes</span>' : '—' ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/messages/' . $r['id']) ?>">Open</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($rooms)): ?>
        <div class="card-body text-muted">No conversations match your filters.</div>
    <?php endif; ?>
</div>
