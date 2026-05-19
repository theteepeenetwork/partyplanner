<?php
/** @var list<array<string,mixed>> $customerEvents */
/** @var array<string,mixed>|null $activeEvent */
if (empty($customerEvents)) {
    return;
}
$activeId = $activeEvent ? (int) $activeEvent['id'] : null;
$redirect = current_url();
?>
<div class="alert alert-light border d-flex flex-wrap align-items-center gap-2 py-2 mb-3 customer-event-switcher">
    <span class="small text-muted mb-0"><i class="fas fa-calendar-check me-1"></i>Planning for:</span>
    <?php if (count($customerEvents) === 1): ?>
        <strong class="mb-0"><?= esc($customerEvents[0]['title'] ?? '') ?></strong>
        <?php if (!empty($customerEvents[0]['date'])): ?>
            <span class="small text-muted">(<?= date('d M Y', strtotime($customerEvents[0]['date'])) ?>)</span>
        <?php endif; ?>
    <?php else: ?>
        <select class="form-select form-select-sm" style="max-width: 300px;" id="customer-active-event-select"
            data-redirect="<?= esc($redirect) ?>">
            <?php foreach ($customerEvents as $ev): ?>
                <option value="<?= (int) $ev['id'] ?>" <?= $activeId === (int) $ev['id'] ? 'selected' : '' ?>>
                    <?= esc($ev['title'] ?? 'Event') ?>
                    <?php if (!empty($ev['date'])): ?>
                        — <?= date('d M Y', strtotime($ev['date'])) ?>
                    <?php endif; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <a href="/profile/events" class="small">All events</a>
        <script>
        (function () {
            const sel = document.getElementById('customer-active-event-select');
            if (!sel) return;
            sel.addEventListener('change', function () {
                const base = '/profile/set-active-event/' + encodeURIComponent(this.value);
                const redir = sel.getAttribute('data-redirect') || '';
                window.location.href = redir ? base + '?redirect=' + encodeURIComponent(redir) : base;
            });
        })();
        </script>
    <?php endif; ?>
</div>
