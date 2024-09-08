<?= $this->include('header') ?>
<main class="container mt-4">
    <h2>Confirm and Pay Deposit</h2>

    <?php if (!empty($cartItems)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td><?= esc($item['service']['title']) ?></td>
                        <td>£<?= esc($item['service']['price']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="text-right">
            <h4>Initial Deposit: £<?= number_format($depositAmount, 2) ?></h4>
        </div>

        <!-- Payment Form -->
        <form method="post" action="<?= site_url('cart/processPayment') ?>" id="payment-form">
            <?= csrf_field() ?>
            <input type="hidden" name="event_id" value="<?= esc($event_id) ?>">
            <input type="hidden" name="payment_intent_id" id="payment_intent_id" value="">

            <!-- Stripe Card Element -->
            <div class="form-group">
                <label for="card-element">Credit or debit card</label>
                <div id="card-element" class="form-control"></div>
                <div id="card-errors" role="alert"></div>
            </div>

            <button type="submit" class="btn btn-success">Pay £<?= number_format($depositAmount, 2) ?> Deposit</button>
        </form>

    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</main>

<!-- Load Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('<?= getenv("STRIPE_PUBLISHABLE_KEY") ?>');
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    cardElement.mount('#card-element');

    const form = document.querySelector('#payment-form');
    const cardErrors = document.getElementById('card-errors');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const { error, paymentIntent } = await stripe.confirmCardPayment("<?= $client_secret ?>", {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: 'Customer Name', // Add dynamic customer name
                }
            }
        });

        if (error) {
            cardErrors.textContent = error.message;
        } else if (paymentIntent.status === 'succeeded') {
            document.getElementById('payment_intent_id').value = paymentIntent.id;
            form.submit(); // Proceed to submit the form to your backend
        }
    });
</script>