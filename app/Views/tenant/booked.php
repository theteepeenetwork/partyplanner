<?php
/**
 * Confirmation (frames 1k/1l) — "date held" honesty (the vendor still
 * confirms), receipt + actions, what-happens-next timeline, and the ONLY
 * account-creation offer in the funnel.
 */
?>
<?= $this->include('tenant_header') ?>
<?php
$bn        = $site['business_name'] ?? 'Storefront';
$firstWord = strtok($bn, ' ');
$paidToday = $payment !== null ? (float) $payment['amount_paid'] : 0.0;
$balance   = (float) ($booking['balance_due'] ?? 0);
$dateLabel = ! empty($event['date']) ? date('D j M Y', strtotime($event['date'])) : 'Date to be agreed';
$where     = trim((string) ($event['postcode'] ?? $event['location'] ?? ''));
$firstName = trim((string) (session()->get('tenant_guest_name') ?? ''));
$phone     = trim((string) ($site['phone'] ?? ''));
$phoneHref = $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';
$titleLine = implode(' + ', array_map(static fn ($i) => $i['service_title'], $items));
?>

<div class="sf-shell" style="padding-top: 26px; max-width: 880px;">
    <div class="sf-tick"><i class="fas fa-check" aria-hidden="true"></i></div>
    <h1 class="sf-confirm-h">Date held — you're nearly booked<?= $firstName !== '' ? ', ' . esc($firstName) : '' ?></h1>
    <p class="sf-confirm-sub">
        Ref <b><?= esc($reference) ?></b> · £<?= esc(number_format($paidToday, 2)) ?> paid<?= $guestEmail !== '' ? ' · receipt sent to ' . esc($guestEmail) : '' ?>
    </p>

    <div class="sf-cols" style="align-items: stretch;">
        <div class="sf-panel" style="box-shadow: var(--sf-sh-card);">
            <p style="font-size: 14px; font-weight: 700; margin: 0 0 2px;"><?= esc($titleLine) ?></p>
            <p style="font-size: 12.5px; color: var(--sf-muted); margin: 0 0 12px;">
                <?= esc($dateLabel) ?><?= $where !== '' ? ' · ' . esc($where) : '' ?>
            </p>

            <div class="sf-quote-card" style="margin: 0 0 12px;">
                <div class="row"><span class="l">Paid today</span><span class="a">£<?= esc(number_format($paidToday, 2)) ?></span></div>
                <div class="row total"><span class="l">Balance after the event</span><span class="a">£<?= esc(number_format($balance, 2)) ?></span></div>
            </div>

            <div class="sf-actions2">
                <a class="sf-btn-outline" href="/booked/<?= (int) $booking['id'] ?>/calendar.ics">
                    <i class="fas fa-calendar-plus" aria-hidden="true"></i>Add to calendar
                </a>
                <?php if ($phone !== ''): ?>
                    <a class="sf-btn-outline" href="<?= esc($phoneHref, 'attr') ?>">
                        <i class="fas fa-phone" aria-hidden="true"></i>Contact <?= esc($firstWord) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="sf-panel" style="box-shadow: var(--sf-sh-card);">
            <h2 class="sf-sec-h" style="margin-bottom: 14px;">What happens next</h2>
            <ol class="sf-timeline">
                <li>
                    <span class="disc">1</span>
                    <b>Soon</b> — <?= esc($firstWord) ?> confirms and you get an email.
                    If they can't, your £<?= esc(number_format($paidToday, 2)) ?> comes straight back.
                </li>
                <li>
                    <span class="disc">2</span>
                    <b>Week before</b> — reminder with the balance (£<?= esc(number_format($balance, 2)) ?>) and arrival time.
                </li>
                <li>
                    <span class="disc">3</span>
                    <b>On the day</b> — they arrive with time to set up. Enjoy the party.
                </li>
            </ol>

            <div style="border-top: 1px solid var(--sf-border); margin-top: 16px; padding-top: 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                <div>
                    <p style="font-size: 13px; font-weight: 700; margin: 0;">Save your booking</p>
                    <p style="font-size: 12px; color: var(--sf-muted); margin: 0;">Create an account with one tap — optional.</p>
                </div>
                <a class="sf-btn-outline" href="/forgot-password" style="flex: none;">Create &rarr;</a>
            </div>
        </div>
    </div>
</div>

<?= $this->include('tenant_footer') ?>
