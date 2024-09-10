<?php

namespace App\Controllers;

use App\Models\PaymentsModel;

class WebhookController extends BaseController
{
    public function stripe()
    {
        // Retrieve the raw request body from Stripe
        $input = @file_get_contents("php://input");
        $event = json_decode($input);

        // Verify the event type is payment_intent.succeeded
        if (isset($event->type) && $event->type == 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object; // The PaymentIntent object

            // Find the payment record in your database using the Payment Intent ID
            $paymentsModel = new PaymentsModel();
            $payment = $paymentsModel->where('payment_intent_id', $paymentIntent->id)->first();

            // Update the payment status to "succeeded"
            if ($payment) {
                $paymentsModel->update($payment['id'], [
                    'payment_status' => 'succeeded'
                ]);
            }
        }

        // Respond with 200 OK to acknowledge receipt of the event
        return $this->response->setStatusCode(200);
    }
}
