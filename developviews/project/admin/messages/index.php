<?php
$filtersActive = (($q ?? '') !== '' && $q !== null)
    || !empty($user_id ?? null)
    || !empty($vendor_id ?? null)
    || !empty($customer_id ?? null)
    || !empty($booking_id ?? null);
$pendingOnly = ($moderation ?? '') === 'pending';
?>
<header class="admin-page-header">
    <h1 class="admin-page-title d-flex align-items-center flex-wrap">
        <span>Messages</span>
        <?php if ($pendingOnly): ?>
            <span class="badge bg-warning text-dark ms-2">Language review</span>
        <?php endif; ?>
    </h1>
    <p class="admin-page-subtitle">Chat rooms between customers and vendors. Filter by participants, booking, or open the language moderation queue.</p>
</header>
<form class="row g-2 admin-filters small" method="get">
    <div class="col-md-3"><input class="form-control" name="q" placeholder="Keyword in message" value="<?= esc($q) ?>"></div>
    <div class="col-md-2"><input class="form-control" name="user_id" placeholder="User ID" value="<?= $user_id ? (int) $user_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="vendor_id" placeholder="Vendor ID" value="<?= $vendor_id ? (int) $vendor_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="customer_id" placeholder="Customer ID" value="<?= $customer_id ? (int) $customer_id : '' ?>"></div>
    <div class="col-md-2"><input class="form-control" name="booking_id" placeholder="Booking ID" value="<?= $booking_id ? (int) $booking_id : '' ?>"></div>
    <?php if ($pendingOnly): ?>
        <input type="hidden" name="moderation" value="pending">
    <?php endif; ?>
    <div class="col-auto d-flex flex-wrap">
        <button class="btn btn-primary me-2 mb-2 mb-md-0" type="submit">Filter</button>
        <a class="btn btn-outline-secondary mb-2 mb-md-0" href="<?= site_url('/admin/messages') ?>">Reset</a>
    </div>
    <div class="col-12">
        <a class="btn btn-sm btn-outline-danger" href="<?= site_url('/admin/messages?moderation=pending') ?>">Pending language review only</a>
    </div>
</form>
<?php if (empty($rooms)): ?>
    <div class="card shadow-sm">
        <div class="admin-empty">
            <div class="admin-empty-icon"><i class="fas fa-comments" aria-hidden="true"></i></div>
            <p class="admin-empty-title">
                <?php if ($pendingOnly): ?>
                    No conversations in the language review queue
                <?php elseif ($filtersActive): ?>
                    No conversations match your filters
                <?php else: ?>
                    No message threads yet
                <?php endif; ?>
            </p>
            <p class="admin-empty-text">
                <?php if ($pendingOnly): ?>
                    All queued messages are cleared, or none are awaiting review.
                <?php elseif ($filtersActive): ?>
                    Remove a filter or reset the form to see the full list of rooms.
                <?php else: ?>
                    Conversations appear when customers and vendors exchange messages about a service.
                <?php endif; ?>
            </p>
            <div class="admin-empty-actions">
                <?php if ($pendingOnly): ?>
                    <a class="btn btn-primary" href="<?= site_url('/admin/messages') ?>">All messages</a>
                <?php elseif ($filtersActive): ?>
                    <a class="btn btn-primary" href="<?= site_url('/admin/messages') ?>">Clear filters</a>
                <?php endif; ?>
                <a class="btn btn-outline-secondary<?= ($pendingOnly || $filtersActive) ? ' ms-2' : '' ?>" href="<?= site_url('/admin') ?>">Dashboard</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm admin-table-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><th>Room</th><th>Vendor</th><th>Customer</th><th>Service</th><th>Flagged</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($rooms as $r): ?>
                    <tr>
                        <td class="text-muted">#<?= (int) $r['id'] ?></td>
                        <td><?= esc($r['vendor_name'] ?? '') ?> <span class="text-muted small">(#<?= (int) ($r['vendor_id'] ?? 0) ?>)</span></td>
                        <td><?= esc($r['customer_name'] ?? '') ?> <span class="text-muted small">(#<?= (int) ($r['customer_id'] ?? 0) ?>)</span></td>
                        <td><?= esc($r['service_title'] ?? '—') ?></td>
                        <td><?= !empty($r['flagged_for_review']) ? '<span class="badge bg-danger">yes</span>' : '—' ?></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/messages/' . $r['id']) ?>">Open</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
