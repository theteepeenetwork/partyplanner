<?= $this->include('header') ?>
<main class="container mt-4">
    <h2>Confirm and Pay Deposit</h2>

    <?php if (!empty($events)):
        $totalDeposit = 0; // Initialize total deposit before the loop
        ?>
        <?php foreach ($events as $event_id => $event):
            $eventTotal = 0; // Initialize the total for the current event
            ?>
            <div class="event-section">
                <h3>Event: <?= esc($event['title']) ?> (Date: <?= esc($event['date']) ?>)</h3>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($event['services'] as $service):
                            $eventTotal += $service['price']; // Add service price to the event total
                            ?>
                            <tr>
                                <td><?= esc($service['title']) ?></td>
                                <td>£<?= esc($service['price']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php
                $eventDeposit = $eventTotal * 0.10; // Calculate 10% deposit for this event
                $totalDeposit += $eventDeposit; // Add to the overall total deposit
                ?>
                <div class="text-right">
                    <h4>Deposit for this event (10%): £<?= number_format($eventDeposit, 2) ?></h4>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="text-right">
            <h3>Total Deposit: £<?= number_format($totalDeposit, 2) ?></h3>
        </div>

        <!-- Payment Form -->
        <form method="post" action="<?= site_url('cart/processPayment') ?>" id="payment-form">
            <?= csrf_field() ?>
            <?php foreach ($events as $event_id => $event): ?>
                <input type="hidden" name="event_ids[]" value="<?= esc($event_id) ?>">
            <?php endforeach; ?>
            <input type="hidden" name="payment_intent_id" id="payment_intent_id" value="">
            <input type="hidden" name="total_deposit" value="<?= number_format($totalDeposit, 2) ?>">

            <!-- Stripe Card Element -->
            <div class="form-group">
                <label for="card-element">Credit or debit card</label>
                <div id="card-element" class="form-control"></div>
                <div id="card-errors" role="alert"></div>
            </div>

            <button type="submit" class="btn btn-success">Pay £<?= number_format($totalDeposit, 2) ?> Deposit</button>
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