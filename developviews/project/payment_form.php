<form id="payment-form" method="POST">
    <?= csrf_field() ?>
    <div id="card-element" style="margin-bottom: 20px;">
        <!-- Stripe Card Element will be inserted here -->
    </div>
    <div id="card-errors" role="alert" style="color: red; margin-bottom: 20px;"></div>
    <button id="submit-payment" class="btn btn-primary">Submit Payment</button>
</form>

<script src="https://js.stripe.com/v3/"></script>

<script>
    const stripe = Stripe('<?= getenv("STRIPE_PUBLISHABLE_KEY") ?>');
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    cardElement.mount('#card-element');

    const form = document.getElementById('payment-form');
    const clientSecret = '<?= $client_secret ?>'; // Use the client_secret from server

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: 'Customer Name'  // You can dynamically fetch the customer name
                }
            }
        });

        if (error) {
            document.getElementById('card-errors').textContent = error.message;
        } else if (paymentIntent.status === 'succeeded') {
            alert('Payment successful!');
            // Optionally, redirect or trigger further actions here
        }
    });
</script>