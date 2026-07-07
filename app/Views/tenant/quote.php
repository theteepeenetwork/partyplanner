<?= $this->include('tenant_header') ?>
<?php
$total   = (float) $quote['total'];
$balance = max(0, round($total - $deposit, 2));
$dateStr = ! empty($quote['event']['date']) ? date('l j F', strtotime($quote['event']['date'])) : 'your date';
$where   = trim((string) ($quote['event']['location'] ?? ''));
?>

<div class="ps-storefront">
<main>
    <section class="sf-sec" style="padding-top: clamp(20px, 3vw, 32px);">
        <div class="container" style="max-width: 640px;">
            <a class="sf-back" href="/service/<?= (int) $service['id'] ?>"><span aria-hidden="true">‹</span> Back</a>

            <h1 class="heading" style="font-size: clamp(26px,4vw,36px); margin: 6px 0 4px;">Your quote</h1>
            <p class="lead" style="margin-bottom: 20px;">
                <?= esc($dateStr) ?><?php if ($where !== ''): ?> · <?= esc($where) ?><?php endif; ?>
                <?php if (! empty($quote['event']['guest_count'])): ?> · <?= (int) $quote['event']['guest_count'] ?> guests<?php endif; ?>
                · <a href="/service/<?= (int) $service['id'] ?>" style="color: var(--sf-primary);">change</a>
            </p>

            <?php if (! empty($quote['warnings'])): ?>
                <div class="sf-flash info"><?= esc(implode(' ', $quote['warnings'])) ?></div>
            <?php endif; ?>

            <ul class="sf-lines">
                <?php foreach ($quote['lines'] as $line): ?>
                    <li>
                        <span class="l-label"><?= esc($line['label']) ?></span>
                        <span class="l-amount"><?= (float) $line['amount'] > 0 ? '£' . esc(number_format((float) $line['amount'], 2)) : 'Free' ?></span>
                    </li>
                <?php endforeach; ?>
                <li class="l-total">
                    <span class="l-label">Total</span>
                    <span class="l-amount">£<?= esc(number_format($total, 2)) ?></span>
                </li>
            </ul>

            <div class="sf-deposit-box">
                <p class="d-headline">£<?= esc(number_format($deposit, 2)) ?> holds your date</p>
                <p class="d-sub"><?= (int) $depositPercent ?>% deposit now — the rest (£<?= esc(number_format($balance, 2)) ?>) after the event.</p>
            </div>

            <form method="get" action="/checkout">
                <button type="submit" class="sf-btn block" style="margin-top: 16px;">
                    Book <?= esc(date('D j M', strtotime($quote['event']['date']))) ?> for £<?= esc(number_format($deposit, 2)) ?>
                </button>
            </form>
            <p class="sf-book-note">Free cancellation for 14 days · full refund</p>
            <p class="sf-book-note">This quote is saved for 48 hours</p>
        </div>
    </section>
</main>
</div>

<?= $this->include('tenant_footer') ?>
