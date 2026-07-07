<?= $this->include('tenant_header') ?>
<?php
// Booking reference like the comp's "BB-2481": business initials + booking id.
$initials = '';
foreach (preg_split('/\s+/', trim((string) ($site['business_name'] ?? ''))) as $w) {
    if ($w !== '' && strlen($initials) < 2) {
        $initials .= strtoupper(mb_substr($w, 0, 1));
    }
}
$reference = ($initials !== '' ? $initials : 'PS') . '-' . (int) $booking['id'];

$paidToday = $payment !== null ? (float) $payment['amount_paid'] : 0.0;
$balance   = (float) ($booking['balance_due'] ?? 0);
$dateStr   = ! empty($event['date']) ? date('l j F Y', strtotime($event['date'])) : 'Date to be agreed';
$firstName = trim((string) (session()->get('tenant_guest_name') ?? ''));
$phone     = trim((string) ($site['phone'] ?? ''));
$phoneHref = $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';
?>

<div class="ps-storefront">
<main>
    <section class="sf-sec" style="padding-top: clamp(28px, 4vw, 44px);">
        <div class="container" style="max-width: 640px; text-align: center;">
            <span class="sf-confirm-tick" aria-hidden="true">✓</span>
            <h1 class="heading" style="font-size: clamp(26px,4vw,38px); margin: 14px 0 6px;">You're booked<?= $firstName !== '' ? ', ' . esc($firstName) : '' ?></h1>
            <p class="lead" style="margin: 0 auto 26px;">Booking <b><?= esc($reference) ?></b> · confirmation sent by email</p>

            <ul class="sf-lines" style="text-align: left;">
                <li><span class="l-label">Date</span><span class="l-amount" style="font-weight:600;"><?= esc($dateStr) ?></span></li>
                <?php foreach ($items as $item): ?>
                    <li><span class="l-label"><?= esc($item['service_title']) ?></span>
                        <span class="l-amount">£<?= esc(number_format((float) $item['price'], 2)) ?></span></li>
                <?php endforeach; ?>
                <li><span class="l-label">Paid today</span><span class="l-amount">£<?= esc(number_format($paidToday, 2)) ?></span></li>
                <li class="l-total"><span class="l-label">Balance after the event</span>
                    <span class="l-amount">£<?= esc(number_format($balance, 2)) ?></span></li>
            </ul>

            <?php if ($phone !== ''): ?>
                <div class="sf-deposit-box" style="margin-top: 24px; text-align: left;">
                    <p class="d-headline">Questions? Talk to <?= esc($site['business_name']) ?></p>
                    <p class="d-sub">They usually reply within the day.</p>
                    <a class="sf-btn" href="<?= esc($phoneHref, 'attr') ?>" style="margin-top: 10px;">
                        <i class="fas fa-phone" aria-hidden="true"></i> Call <?= esc($phone) ?>
                    </a>
                </div>
            <?php endif; ?>

            <p class="sf-book-note" style="margin-top: 22px;"><a href="/" style="color: var(--sf-primary); font-weight: 700;">Back to <?= esc($site['business_name']) ?></a></p>
        </div>
    </section>
</main>
</div>

<?= $this->include('tenant_footer') ?>
