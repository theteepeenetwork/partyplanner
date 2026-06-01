<div class="admin-toolbar d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="admin-page-title mb-0">Conversation #<?= (int) $room['id'] ?></h1>
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
<div class="d-flex flex-wrap mb-3">
    <?php if (empty($room['flagged_for_review'])): ?>
        <form method="post" action="<?= site_url('/admin/messages/' . $room['id'] . '/flag') ?>" class="me-2 mb-2">
            <?= csrf_field() ?>
            <button class="btn btn-outline-warning btn-sm" type="submit">Flag conversation</button>
        </form>
    <?php else: ?>
        <form method="post" action="<?= site_url('/admin/messages/' . $room['id'] . '/unflag') ?>" class="me-2 mb-2">
            <?= csrf_field() ?>
            <button class="btn btn-outline-secondary btn-sm" type="submit">Clear room flag</button>
        </form>
    <?php endif; ?>
    <a class="btn btn-sm btn-outline-danger mb-2" href="<?= site_url('/admin/messages?moderation=pending') ?>">All pending language reviews</a>
</div>
<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Thread &amp; moderation</div>
    <div class="list-group list-group-flush">
        <?php foreach ($messages as $m): ?>
            <?php
            $st = $m['moderation_status'] ?? 'clean';
            $pending = ($st === \App\Libraries\ChatModeration::STATUS_PENDING);
            ?>
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 me-3">
                        <div class="small text-muted"><?= esc($m['created_at'] ?? '') ?> — sender #<?= (int) ($m['sender_id'] ?? 0) ?></div>
                        <div class="mt-1"><?= nl2br(esc($m['message'] ?? '')) ?></div>
                        <?= view('partials/chat_moderation_meta', ['msg' => $m]) ?>
                        <?php if (!empty($m['profanity_matches'])): ?>
                            <div class="small text-muted mt-1">Matched terms (lowercase): <code><?= esc($m['profanity_matches']) ?></code></div>
                        <?php endif; ?>
                        <?php if ($pending && !empty($m['original_message'])): ?>
                            <details class="mt-2 small">
                                <summary class="text-danger">View original text (moderator only)</summary>
                                <div class="border rounded p-2 mt-1 bg-light"><?= nl2br(esc($m['original_message'])) ?></div>
                            </details>
                        <?php endif; ?>
                    </div>
                    <div class="text-nowrap">
                        <?php if ($pending): ?>
                            <form method="post" action="<?= site_url('/admin/messages/moderate/' . (int) $m['id'] . '/approve') ?>" class="mb-2">
                                <?= csrf_field() ?>
                                <label class="form-label small mb-0">Note (optional)</label>
                                <input type="text" name="admin_note" class="form-control form-control-sm mb-1" maxlength="2000" placeholder="Reviewer note">
                                <button type="submit" class="btn btn-sm btn-success w-100">Accept &amp; show as sent</button>
                            </form>
                            <form method="post" action="<?= site_url('/admin/messages/moderate/' . (int) $m['id'] . '/reject') ?>">
                                <?= csrf_field() ?>
                                <label class="form-label small mb-0">Note (optional)</label>
                                <input type="text" name="admin_note" class="form-control form-control-sm mb-1" maxlength="2000" placeholder="Reason">
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Mark as not sent?');">Reject &amp; not sent</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= site_url('/admin/messages/delete/' . (int) $m['id']) ?>" class="mt-2" onsubmit="return confirm('Remove this message entirely?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-secondary" type="submit">Delete message</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($messages)): ?>
            <div class="list-group-item text-muted">No messages in this room.</div>
        <?php endif; ?>
    </div>
</div>
