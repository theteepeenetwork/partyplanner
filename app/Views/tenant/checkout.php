<?php
/**
 * Checkout (frames 1i/1j) — the anxiety-peak screen. Deposit only, guest
 * checkout mandatory, refund tiers in plain English, "held by PartySmith"
 * escrow copy. Payment wiring (Stripe PaymentElement / simulated) unchanged.
 */
?>
<?= $this->include('tenant_header') ?>
<?php
$bn        = $site['business_name'] ?? 'Storefront';
$total     = (float) $quote['total'];
$balance   = max(0, round($total - $deposit, 2));
$dateLabel = ! empty($quote['event']['date']) ? date('D j M Y', strtotime($quote['event']['date'])) : 'your date';
$dateShort = ! empty($quote['event']['date']) ? date('D j M', strtotime($quote['event']['date'])) : 'your date';
$where     = trim((string) ($quote['event']['postcode'] ?? ''));
$firstWord = strtok($bn, ' ');
?>

<div class="sf-shell" style="padding-top: 18px;">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px;">
        <h1 style="font-size: 19px; font-weight: 700; margin: 0; letter-spacing: -0.01em;">Hold your date</h1>
        <span class="sf-lockline"><i class="fas fa-lock" aria-hidden="true"></i>Secure checkout</span>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="sf-flash error" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="sf-cols">
        <div>
            <p style="font-size: 13px; color: var(--sf-muted); margin: 0 0 16px;">
                No account needed — you can create one after payment to track your booking.
            </p>

            <form method="post" action="/checkout" id="sfCheckoutForm">
                <?= csrf_field() ?>
                <input type="hidden" name="payment_intent_id" id="payment_intent_id" value="">

                <h2 class="sf-sec-h" style="margin-bottom: 10px;">Your details <span style="font-weight: 400; color: var(--sf-muted); font-size: 12.5px;">— no account needed</span></h2>
                <div class="sf-2col">
                    <label class="sf-field">
                        <span>Your name</span>
                        <input class="sf-input" type="text" name="guest_name" required maxlength="100" autocomplete="name">
                    </label>
                    <label class="sf-field">
                        <span>Mobile — for the confirmation text</span>
                        <input class="sf-input" type="tel" name="guest_phone" maxlength="32" autocomplete="tel">
                    </label>
                </div>
                <label class="sf-field">
                    <span>Email — your receipt goes here</span>
                    <input class="sf-input" type="email" name="guest_email" required maxlength="255" autocomplete="email">
                </label>

                <h2 class="sf-sec-h" style="margin: 18px 0 10px;">Card</h2>
                <div class="sf-card" style="padding: 14px;">
                    <?php if (! empty($stripeEnabled) && ! empty($stripeClientSecret)): ?>
                        <div id="payment-element"></div>
                        <p class="sf-microcopy" style="text-align: left;">Secure payment — your card details never touch our servers.</p>
                    <?php else: ?>
                        <p class="sf-microcopy" style="text-align: left; margin: 0;">Payment processing is simulated (Stripe not configured on this environment).</p>
                    <?php endif; ?>
                    <div id="payment-errors" class="sf-flash error" style="display: none;" role="alert"></div>
                </div>

                <button type="submit" class="sf-btn block" id="payBtn" style="margin-top: 16px;">
                    <i class="fas fa-lock" aria-hidden="true"></i>&nbsp;Pay £<?= esc(number_format($deposit, 2)) ?> &amp; hold the date
                </button>
                <p class="sf-microcopy">
                    <?= esc($firstWord) ?> confirms your booking — you get a text.<br>
                    Deposit held by PartySmith until they do.
                </p>
            </form>
        </div>

        <aside>
            <div class="sf-panel">
                <div class="sf-summary">
                    <?php if (! empty($thumbUrl)): ?><img src="<?= esc($thumbUrl, 'attr') ?>" alt=""><?php endif; ?>
                    <div style="min-width: 0;">
                        <p class="t"><?= esc($service['title']) ?></p>
                        <p class="s"><?= esc($dateLabel) ?><?= $where !== '' ? ' · ' . esc($where) : '' ?> · <?= esc($bn) ?></p>
                    </div>
                    <a class="edit" href="/service/<?= (int) $service['id'] ?>?<?= esc(http_build_query(array_filter(['date' => $quote['event']['date'] ?? '', 'postcode' => $where, 'guests' => $quote['event']['guest_count'] ?? null])), 'attr') ?>">Edit</a>
                </div>

                <div class="sf-quote-card" style="margin: 14px 0 0;">
                    <?php foreach ($quote['lines'] as $line): ?>
                        <?php if (($line['code'] ?? '') === 'platform_commission') { continue; } ?>
                        <div class="row">
                            <span class="l"><?= esc($line['label']) ?></span>
                            <span class="a"><?= (float) $line['amount'] > 0 ? '£' . esc(number_format((float) $line['amount'], 2)) : 'Free' ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="row total"><span class="l">Total</span><span class="a">£<?= esc(number_format($total, 2)) ?></span></div>
                </div>

                <div class="sf-plan" style="margin-top: 14px;">
                    <div class="top">
                        <i class="fas fa-shield-halved" aria-hidden="true"></i>
                        <div>
                            <div class="amt">£<?= esc(number_format($deposit, 2)) ?> today</div>
                            <div class="sub"><?= (int) $depositPercent ?>% deposit — holds <?= esc($dateShort) ?> for you</div>
                        </div>
                    </div>
                    <div class="bal"><span>Balance — due after the event</span><span class="a">£<?= esc(number_format($balance, 2)) ?></span></div>
                    <div class="sf-refunds">
                        <b>If you cancel:</b> full refund up to 14 days before the event · 50% refund within 14 days ·
                        full refund any time if the vendor cancels or can't deliver.
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php if (! empty($stripeEnabled) && ! empty($stripeClientSecret) && ! empty($stripePublishableKey)): ?>
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    const stripe = Stripe(<?= json_encode($stripePublishableKey) ?>);
    const elements = stripe.elements({ clientSecret: <?= json_encode($stripeClientSecret) ?> });
    const paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');

    const form = document.getElementById('sfCheckoutForm');
    const btn = document.getElementById('payBtn');
    const errBox = document.getElementById('payment-errors');
    let confirmed = false;

    form.addEventListener('submit', async function (e) {
        if (confirmed) return;
        e.preventDefault();
        if (!form.reportValidity()) return;
        btn.disabled = true;
        errBox.style.display = 'none';

        const { error, paymentIntent } = await stripe.confirmPayment({ elements, redirect: 'if_required' });

        if (error) {
            errBox.textContent = error.message || 'Payment failed — please try again.';
            errBox.style.display = 'block';
            btn.disabled = false;
            return;
        }

        document.getElementById('payment_intent_id').value = paymentIntent.id;
        confirmed = true;
        form.submit();
    });
})();
</script>
<?php endif; ?>

<?= $this->include('tenant_footer') ?>
