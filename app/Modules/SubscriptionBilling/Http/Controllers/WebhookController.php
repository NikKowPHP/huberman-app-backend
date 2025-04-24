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

        // Handle customer.subscription.updated event (Trial Ends -> Active)
        if ($payload['type'] === 'customer.subscription.updated' && isset($payload['data']['object']['trial_end']) && $payload['data']['object']['status'] === 'active') {
            $this->handleCustomerSubscriptionUpdatedTrialEnded($payload);
        }

        // Handle invoice.payment_succeeded event (Renewal)
        if ($payload['type'] === 'invoice.payment_succeeded') {
            $this->handleInvoicePaymentSucceeded($payload);
        }

        // Handle invoice.payment_failed event
        if ($payload['type'] === 'invoice.payment_failed') {
            $this->handleInvoicePaymentFailed($payload);
        }

        // Handle customer.subscription.updated event (Cancel at Period End)
        if ($payload['type'] === 'customer.subscription.updated' && isset($payload['data']['object']['cancel_at_period_end']) && $payload['data']['object']['cancel_at_period_end'] === true) {
            $this->handleCustomerSubscriptionUpdatedCancel($payload);
        }

        // Handle customer.subscription.deleted event (Immediate Cancel / Final Failure)
        if ($payload['type'] === 'customer.subscription.deleted') {
            $this->handleCustomerSubscriptionDeleted($payload);
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

    /**
     * Handle customer.subscription.updated event when trial ends.
     *
     * @param  array  $payload
     * @return void
     */
    protected function handleCustomerSubscriptionUpdatedTrialEnded(array $payload)
    {
        $subscriptionId = $payload['data']['object']['id'];
        $subscription = \App\Modules\SubscriptionBilling\Models\Subscription::where('stripe_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->stripe_status = $payload['data']['object']['status'];
            $subscription->trial_ends_at = null;
            $subscription->ends_at = null; // Or set to the end of the current period
            $subscription->save();
        }
    }

    /**
     * Handle invoice.payment_succeeded event.
     *
     * @param  array  $payload
     * @return void
     */
    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        $subscriptionId = $payload['data']['object']['subscription'];
        $subscription = \App\Modules\SubscriptionBilling\Models\Subscription::where('stripe_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->ends_at = now()->addSeconds($payload['data']['object']['lines']['data'][0]['period']['end'] - time());
            $subscription->save();
        }
    }

    /**
     * Handle invoice.payment_failed event.
     *
     * @param  array  $payload
     * @return void
     */
    protected function handleInvoicePaymentFailed(array $payload)
    {
        $subscriptionId = $payload['data']['object']['subscription'];
        $subscription = \App\Modules\SubscriptionBilling\Models\Subscription::where('stripe_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->stripe_status = 'past_due';
            $subscription->save();
        }
    }

    /**
     * Handle customer.subscription.updated event when cancelled.
     *
     * @param  array  $payload
     * @return void
     */
    protected function handleCustomerSubscriptionUpdatedCancel(array $payload)
    {
        $subscriptionId = $payload['data']['object']['id'];
        $subscription = \App\Modules\SubscriptionBilling\Models\Subscription::where('stripe_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->stripe_status = 'canceled';
            $subscription->ends_at =  now()->addSeconds($payload['data']['object']['current_period_end'] - time());
            $subscription->save();
        }
    }

    /**
     * Handle customer.subscription.deleted event.
     *
     * @param  array  $payload
     * @return void
     */
    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
         $subscriptionId = $payload['data']['object']['id'];
         $subscription = \App\Modules\SubscriptionBilling\Models\Subscription::where('stripe_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->stripe_status = 'canceled';
            $subscription->ends_at = now();
            $subscription->save();
        }
    }
}
