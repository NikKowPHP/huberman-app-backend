<?php

namespace App\Modules\SubscriptionBilling\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Laravel\Cashier\Events\SubscriptionStarted;
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

        if ($payload['type'] === 'checkout.session.completed') {
            $this->handleCheckoutSessionCompleted($payload);
        }

        return parent::handleWebhook($payload);
    }

    /**
     * Handle checkout session completed event.
     *
     * @param  array  $payload
     * @return void
     */
    protected function handleCheckoutSessionCompleted(array $payload)
    {
        $customerId = $payload['data']['object']['customer'];
        $customerEmail = $payload['data']['object']['customer_email'];
        $subscriptionId = $payload['data']['object']['subscription'];

        $user = User::where('email', $customerEmail)->first();

        if ($user) {
            $user->subscriptions()->create([
                'name' => 'default',
                'stripe_id' => $subscriptionId,
                'stripe_customer_id' => $customerId,
                'stripe_status' => 'trialing', // Or 'active' based on the plan
                // Add other necessary data based on Stripe's webhook payload
            ]);

            $subscription = $user->subscriptions()->first();
            Event::dispatch(new SubscriptionStarted($subscription));

            // Update the user's stripe_id if it's not already set
            if (!$user->stripe_id) {
                $user->update(['stripe_id' => $customerId]);
            }
            else {
                // If stripe_id is already set, ensure it matches the customer id
                if ($user->stripe_id !== $customerId) {
                    $user->update(['stripe_id' => $customerId]);
                }
            }
        }
    }
}
