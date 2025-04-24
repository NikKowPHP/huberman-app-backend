<?php

namespace App\Modules\SubscriptionBilling\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class WebhookController extends CashierController
{
    /**
     * Handle a Stripe webhook.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleWebhook(array $payload)
    {
        // Handle specific webhook events here
        // For example:
        // if ($payload['type'] === 'checkout.session.completed') {
        //     // Handle checkout session completed event
        // }

        return parent::handleWebhook($payload);
    }
}
