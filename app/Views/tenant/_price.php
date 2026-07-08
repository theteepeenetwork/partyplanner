<?php
/**
 * Shared "from" price — one template for all three pricing shapes (flat,
 * per-guest, per-hour). Consistent weight/colour everywhere it renders:
 *   "from" regular · amount semibold · unit qualifier regular + muted.
 *
 * Expects $from = ['amount' => float, 'per' => '' | 'guest' | 'hour' | …].
 */
$amount = (float) ($from['amount'] ?? 0);
$per    = trim((string) ($from['per'] ?? ''));
if ($amount <= 0) {
    return;
}
?>
<span class="sf-price">
    <span class="lead">from</span>
    <span class="amt">£<?= esc(number_format($amount, $amount == (int) $amount ? 0 : 2)) ?></span>
    <?php if ($per !== ''): ?><span class="per">/<?= esc($per) ?></span><?php endif; ?>
</span>
