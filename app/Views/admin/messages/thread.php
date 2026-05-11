<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Conversation #<?= (int) $room['id'] ?></h1>
    <a class="btn btn-outline-secondary" href="<?= site_url('/admin/messages') ?>">Back</a>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm"><div class="card-body small">
            <p class="mb-1"><strong>Vendor:</strong> <?= esc($room['vendor_name'] ?? '') ?> (#<?= (int) ($room['vendor_id'] ?? 0) ?>)</p>
            <p class="mb-1"><strong>Customer:</strong> <?= esc($room['customer_name'] ?? '') ?> (#<?= (int) ($room['customer_id'] ?? 0) ?>)</p>
            <p class="mb-0"><strong>Service:</strong> <?= esc($room['service_title'] ?? '—') ?></p>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm"><div class="card-body small">
            <p class="mb-1"><strong>Recent customer bookings</strong> (context)</p>
            <ul class="mb-0 ps-3">
                <?php foreach (($context['recent_bookings'] ?? []) as $bk): ?>
                    <li><a href="<?= site_url('/admin/bookings/' . $bk['id']) ?>">Booking #<?= (int) $bk['id'] ?></a> — <?= esc($bk['status'] ?? '') ?></li>
                <?php endforeach; ?>
                <?php if (empty($context['recent_bookings'])): ?><li class="text-muted">None</li><?php endif; ?>
            </ul>
        </div></div>
    </div>
</div>
<div class="d-flex gap-2 mb-3">
    <?php if (empty($room['flagged_for_review'])): ?>
        <form method="post" action="<?= site_url('/admin/messages/' . $room['id'] . '/flag') ?>">
            <?= csrf_field() ?>
            <button class="btn btn-outline-warning btn-sm" type="submit">Flag for review</button>
        </form>
    <?php else: ?>
        <form method="post" action="<?= site_url('/admin/messages/' . $room['id'] . '/unflag') ?>">
            <?= csrf_field() ?>
            <button class="btn btn-outline-secondary btn-sm" type="submit">Clear flag</button>
        </form>
    <?php endif; ?>
</div>
<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">Thread (read-only)</div>
    <div class="list-group list-group-flush">
        <?php foreach ($messages as $m): ?>
            <div class="list-group-item d-flex justify-content-between align-items-start">
                <div>
                    <div class="small text-muted"><?= esc($m['created_at'] ?? '') ?> — sender #<?= (int) ($m['sender_id'] ?? 0) ?></div>
                    <div><?= nl2br(esc($m['message'] ?? '')) ?></div>
                </div>
                <form method="post" action="<?= site_url('/admin/messages/delete/' . $m['id']) ?>" onsubmit="return confirm('Remove this message?');">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>
        <?php if (empty($messages)): ?>
            <div class="list-group-item text-muted">No messages in this room.</div>
        <?php endif; ?>
    </div>
</div>
