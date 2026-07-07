<?= $this->include('tenant_header') ?>
<?php
$total   = (float) $quote['total'];
$dateStr = ! empty($quote['event']['date']) ? date('D j M', strtotime($quote['event']['date'])) : 'your date';
$where   = trim((string) ($quote['event']['location'] ?? ''));
?>

<div class="ps-storefront">
<main>
    <section class="sf-sec" style="padding-top: clamp(20px, 3vw, 32px);">
        <div class="container" style="max-width: 640px;">
            <a class="sf-back" href="/service/<?= (int) $service['id'] ?>"><span aria-hidden="true">‹</span> Back</a>

            <h1 class="heading" style="font-size: clamp(26px,4vw,36px); margin: 6px 0 4px;">Pay your deposit</h1>
            <p class="lead" style="margin-bottom: 18px;">
                <?= esc($service['title']) ?> · <?= esc($dateStr) ?><?php if ($where !== ''): ?> · <?= esc($where) ?><?php endif; ?>
                · £<?= esc(number_format($total, 2)) ?> total
            </p>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="sf-flash error" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <div class="sf-deposit-box" style="margin-bottom: 20px;">
                <p class="d-headline">Due today · £<?= esc(number_format($deposit, 2)) ?></p>
                <p class="d-sub"><?= (int) $depositPercent ?>% deposit — the rest is settled with <?= esc($site['business_name']) ?> after the event.</p>
            </div>

            <form method="post" action="/checkout" id="tenantCheckoutForm">
                <?= csrf_field() ?>
                <input type="hidden" name="payment_intent_id" id="payment_intent_id" value="">

                <label class="sf-field">
                    <span>Your name</span>
                    <input type="text" name="guest_name" required maxlength="100" autocomplete="name">
                </label>
                <label class="sf-field">
                    <span>Email — your confirmation goes here</span>
                    <input type="email" name="guest_email" required maxlength="255" autocomplete="email">
                </label>
                <label class="sf-field">
                    <span>Mobile (optional)</span>
                    <input type="tel" name="guest_phone" maxlength="32" autocomplete="tel">
                </label>

                <div class="sf-card-box">
                    <p class="sf-card-box-head">Card details <span>POWERED BY STRIPE</span></p>
                    <?php if (! empty($stripeEnabled) && ! empty($stripeClientSecret)): ?>
                        <div id="payment-element"></div>
                        <p class="sf-book-note" style="text-align: left;">Secure payment — your card details never touch our servers.</p>
                    <?php else: ?>
                        <p class="sf-book-note" style="text-align: left;">Payment processing is simulated (Stripe not configured on this environment).</p>
                    <?php endif; ?>
                    <div id="payment-errors" class="sf-flash error" style="display: none;" role="alert"></div>
                </div>

                <button type="submit" class="sf-btn block" id="payBtn">Pay £<?= esc(number_format($deposit, 2)) ?> deposit</button>
            </form>

            <div class="sf-next">
                <h2>What happens next</h2>
                <ol>
                    <li><b>Instant confirmation</b> by email — your date is locked.</li>
                    <li><b><?= esc($site['business_name']) ?> gets in touch</b> to agree the details.</li>
                    <li><b>Pay the rest after the event</b> — £<?= esc(number_format(max(0, $total - $deposit), 2)) ?>, direct with them.</li>
                </ol>
            </div>
        </div>
    </section>
</main>
</div>

<?php if (! empty($stripeEnabled) && ! empty($stripeClientSecret) && ! empty($stripePublishableKey)): ?>
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    const stripe = Stripe(<?= json_encode($stripePublishableKey) ?>);
    const elements = stripe.elements({ clientSecret: <?= json_encode($stripeClientSecret) ?> });
    const paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');

    const form = document.getElementById('tenantCheckoutForm');
    const btn = document.getElementById('payBtn');
    const errBox = document.getElementById('payment-errors');
    let confirmed = false;

    form.addEventListener('submit', async function (e) {
        if (confirmed) return; // second pass: post to the server with the PI id
        e.preventDefault();
        if (!form.reportValidity()) return;
        btn.disabled = true;
        errBox.style.display = 'none';

        const { error, paymentIntent } = await stripe.confirmPayment({
            elements,
            redirect: 'if_required'
        });

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
