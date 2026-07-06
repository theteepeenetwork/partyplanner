<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container" style="max-width: 700px;">
        <h3 class="mb-2">Checkout</h3>
        <p class="text-muted mb-4">Pay your deposit to submit booking requests to vendors.</p>

        
        <div class="dash-card mb-3">
            <h5><i class="fas fa-calendar text-primary me-2"></i><?= esc($event['title']) ?></h5>
            <div class="text-muted small">
                <?php if (!empty($event['date'])): ?><i class="fas fa-calendar-alt me-1"></i><?= date('d M Y', strtotime($event['date'])) ?><?php endif; ?>
                <?php if (!empty($event['location'])): ?><span class="ms-2"><i class="fas fa-map-marker-alt me-1"></i><?= esc($event['location']) ?></span><?php endif; ?>
            </div>
        </div>

        <div class="dash-card mb-3">
            <h6 class="mb-3">Order Summary</h6>
            <?php foreach ($basketItems as $item): ?>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <div>
                        <span class="fw-bold"><?= esc($item['service_title']) ?></span>
                        <?php if (!empty($item['package_name'])): ?>
                            <span class="text-muted small ms-1">(<?= esc($item['package_name']) ?>)</span>
                        <?php endif; ?>
                        <div class="small text-muted">Est. total £<?= number_format((float) $item['estimated_total'], 2) ?> · Deposit £<?= number_format((float) $item['deposit_amount'], 2) ?></div>
                    </div>
                    <span class="text-nowrap">£<?= number_format($item['deposit_amount'], 2) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="d-flex justify-content-between pt-3">
                <span class="fw-bold">Total Deposit (<?= (int) ($depositPercent ?? 10) ?>%)</span>
                <span class="fw-bold text-primary fs-5">£<?= number_format($totalDeposit, 2) ?></span>
            </div>
        </div>

        <div class="dash-card mb-3">
            <h6 class="mb-3"><i class="fas fa-credit-card text-success me-2"></i>Payment</h6>
            <form method="post" action="/event/checkout/process/<?= $event['id'] ?>" id="checkout-form">
                <?= csrf_field() ?>
                <input type="hidden" name="payment_intent_id" id="payment_intent_id" value="">
                <div class="mb-3">
                    <label class="form-label">Payment plan</label>
                    <select name="payment_plan" class="form-select">
                        <option value="single">Single balance payment after deposit</option>
                        <option value="instalments">Two instalments for balance</option>
                    </select>
                </div>
                <?php if (!empty($stripeEnabled) && !empty($stripeClientSecret)): ?>
                    <div id="payment-element" class="mb-3">
                    <p class="small text-muted">Secure card payment via Stripe.</p>
                <?php else: ?>
                    <p class="text-muted small">Payment processing is simulated (Stripe not configured).</p>
                    <div class="mb-3">
                        <label class="form-label">Card Number</label>
                        <input type="text" class="form-control" value="4242 4242 4242 4242" disabled>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info small">
                    <i class="fas fa-info-circle me-1"></i>
                    By paying the deposit, your booking request will be sent to each vendor for approval.
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/event/basket/<?= $event['id'] ?>" class="btn btn-outline-secondary">Back to Basket</a>
                    <button type="submit" class="btn btn-success btn-lg" id="submit-checkout">
                        <i class="fas fa-lock me-1"></i>Pay Deposit £<?= number_format($totalDeposit, 2) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</main>

<?php if (!empty($stripeEnabled) && !empty($stripeClientSecret) && !empty($stripePublishableKey)): ?>
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    const stripe = Stripe(<?= json_encode($stripePublishableKey) ?>);
    const elements = stripe.elements({ clientSecret: <?= json_encode($stripeClientSecret) ?> });
    const paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');
    const form = document.getElementById('checkout-form');
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('submit-checkout');
        btn.disabled = true;
        const { error, paymentIntent } = await stripe.confirmPayment({
            elements,
            confirmParams: { return_url: window.location.href },
            redirect: 'if_required',
        });
        if (error) {
            alert(error.message);
            btn.disabled = false;
            return;
        }
        if (paymentIntent) {
            document.getElementById('payment_intent_id').value = paymentIntent.id;
        }
        form.submit();
    });
})();
</script>
<?php endif; ?>

<?= $this->include('footer') ?>
