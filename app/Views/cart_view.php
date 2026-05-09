<?= $this->include('header') ?>

<main class="container mt-4">
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
    <h2>Your Cart</h2>

<<<<<<< HEAD
    <?php if (!empty($cartItems)): 
        $total = 0; // Initialize total before the loop
        ?>
        <table class="table">
            <thead>
                <!-- Add table headings -->
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): 
                    $subtotal = $item['service']['price'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?= esc($item['service']['title']) ?></td>
                        <td>$<?= esc($item['service']['price']) ?></td>
                        <td>
                            <a href="<?= base_url('cart/remove/' . esc($item['id'])) ?>" class="btn btn-danger btn-sm">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="text-right">
            <h4>Total: $<?= number_format($total, 2) ?></h4>

            <?php if (!empty($events)): ?>
            <form method="post" action="<?= site_url('cart/submit') ?>" id="payment-form">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="event_id">Select Event:</label>
                    <select class="form-control" id="event_id" name="event_id">
                        <?php foreach ($events as $event): ?>
                            <option value="<?= esc($event['id']) ?>"><?= esc($event['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Payment card fields -->
                <div class="form-group">
                    <label for="card-element">Credit or debit card</label>
                    <div id="card-element" class="form-control"></div>
                    <div id="card-errors" role="alert"></div>
                </div>

                <button type="submit" class="btn btn-success">Submit to Vendors</button>
            </form>

            <?php else: ?>
                <p>You need to <a href="<?= site_url('event/create') ?>">create an event</a> before submitting your cart.</p>
            <?php endif; ?>
=======
    <?php if (!empty($events)): ?>
        <?php foreach ($events as $eventId => $event): ?>
            <div class="event-section">
                <h3>Event: <?= esc($event['title']) ?> (Date: <?= esc($event['date']) ?>)</h3>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Price</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($event['services'] as $service): ?>
                            <tr>
                                <td><?= esc($service['title']) ?></td>
                                <td>£<?= esc($service['price']) ?></td>
                                <td><?= esc($service['start_time']) ?></td>
                                <td><?= esc($service['end_time']) ?></td>
                                <td>
                                    <a href="<?= base_url('cart/remove/' . esc($service['id'])) ?>" class="btn btn-danger btn-sm">
                                        Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

        <div class="text-right">

            <form method="post" action="<?= site_url('cart/submitToVendors') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success">Submit All Events to Vendors</button>
            </form>
>>>>>>> 648c0f070acc4c3ee38e07810be1a97650ad6ff6
        </div>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</main>

<<<<<<< HEAD
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Get the client secret from your server
    const clientSecret = "<?= $clientSecret ?>";  // This needs to be passed from your controller when rendering the view

    const stripe = Stripe('<?= getenv("STRIPE_PUBLISHABLE_KEY"); ?>');
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    cardElement.mount('#card-element');

    const form = document.querySelector('#payment-form');
    const submitButton = document.querySelector('button[type="submit"]');
    const cardErrors = document.getElementById('card-errors');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        submitButton.disabled = true; // Disable the submit button to prevent multiple clicks

        // Confirm the payment with the clientSecret from the server
        const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: 'Customer Name', // You can pass the customer's name dynamically
                }
            }
        });

        if (error) {
            // Display error message in the card-errors div
            cardErrors.textContent = error.message;
            submitButton.disabled = false; // Re-enable the submit button if there's an error
        } else if (paymentIntent.status === 'succeeded') {
            // Payment successful, allow form submission to proceed
            form.submit(); // Now submit the form to your server-side handler
        }
    });
</script>
=======
<?= $this->include('footer') ?>
>>>>>>> 648c0f070acc4c3ee38e07810be1a97650ad6ff6
