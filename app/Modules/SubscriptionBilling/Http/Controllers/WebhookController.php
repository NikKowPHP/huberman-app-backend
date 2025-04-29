<?php

namespace App\Modules\SubscriptionBilling\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Laravel\Cashier\Events\SubscriptionStarted;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use App\Modules\SubscriptionBilling\Services\AppleSubscriptionService;
use App\Events\SubscriptionSubscribed;
use App\Events\SubscriptionRenewed;

class WebhookController extends CashierController
{
    private $appleSubscriptionService;

    public function __construct(AppleSubscriptionService $appleSubscriptionService)
    {
        $this->appleSubscriptionService = $appleSubscriptionService;
    }

    /**
     * Handle a Stripe webhook.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);

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

        return parent::handleWebhook($request);
    }

    /**
     * Handle checkout session completed event.
     *
     * @param  array  $payload
     * @return void
     */
    protected function handleCheckoutSessionCompleted(array $payload)
    {
        $customerId = $payload['data']['object']['customer'] ?? null;
        $customerEmail = $payload['data']['object']['customer_email'] ?? null;
        $subscriptionId = $payload['data']['object']['subscription'] ?? null;

        if (!$customerId || !$customerEmail || !$subscriptionId) {
            \Log::warning('Missing data in checkout.session.completed event', $payload);
            return;
        }

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
        } else {
            \Log::warning('User not found for checkout.session.completed event', $payload);
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
        $subscriptionId = $payload['data']['object']['id'] ?? null;
        if (!$subscriptionId) {
            \Log::warning('Missing data in customer.subscription.updated event (trial end)', $payload);
            return;
        }
        $subscription = \App\Modules\SubscriptionBilling\Models\Subscription::where('stripe_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->stripe_status = $payload['data']['object']['status'] ?? 'active';
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
        $subscriptionId = $payload['data']['object']['subscription'] ?? null;
         if (!$subscriptionId) {
            \Log::warning('Missing data in invoice.payment_succeeded event', $payload);
            return;
        }
        $subscription = \App\Modules\SubscriptionBilling\Models\Subscription::where('stripe_id', $subscriptionId)->first();

        if ($subscription) {
            $periodEnd = $payload['data']['object']['lines']['data'][0]['period']['end'] ?? time();
            $subscription->ends_at = now()->addSeconds($periodEnd - time());
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
        $subscriptionId = $payload['data']['object']['subscription'] ?? null;
         if (!$subscriptionId) {
            \Log::warning('Missing data in invoice.payment_failed event', $payload);
            return;
        }
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
        $subscriptionId = $payload['data']['object']['id'] ?? null;
         if (!$subscriptionId) {
            \Log::warning('Missing data in customer.subscription.updated event (cancel)', $payload);
            return;
        }
        $subscription = \App\Modules\SubscriptionBilling\Models\Subscription::where('stripe_id', $subscriptionId)->first();

        if ($subscription) {
            $periodEnd = $payload['data']['object']['current_period_end'] ?? time();
            $subscription->stripe_status = 'canceled';
            $subscription->ends_at =  now()->addSeconds($periodEnd - time());
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
         $subscriptionId = $payload['data']['object']['id'] ?? null;
          if (!$subscriptionId) {
            \Log::warning('Missing data in customer.subscription.deleted event', $payload);
            return;
        }
         $subscription = \App\Modules\SubscriptionBilling\Models\Subscription::where('stripe_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->stripe_status = 'canceled';
            $subscription->ends_at = now();
            $subscription->save();
        }
    }

    public function handleAppleWebhook(Request $request)
    {
        try {
            $jws = $request->getContent();
            $data = $this->appleSubscriptionService->decodeAndVerifyJWS($jws);

            if (isset($data['notificationType'])) {
                if ($data['notificationType'] === 'SUBSCRIBED' || $data['notificationType'] === 'DID_RENEW') {
                    $this->handleAppleSubscribed($data);
                } elseif ($data['notificationType'] === 'DID_CHANGE_RENEWAL_STATUS') {
                    $this->appleSubscriptionService->handleDidChangeRenewalStatus($jws);
                }
            }

            // TODO: Process the data
            \Log::info('Apple Webhook Data', $data);

            return response('Apple Webhook Received', 200);
        } catch (\Exception $e) {
            \Log::error('Apple Webhook Error: ' . $e->getMessage());
            return response('Invalid Apple Webhook', 400);
        }
    }

    protected function handleAppleRenewalStatusChange(array $data)
    {
        $originalTransactionId = $data['originalTransactionId'] ?? null;

        $user = User::where('appstore_transaction_id', $originalTransactionId)->first();

        if ($user) {
            $user->subscriptions()->update([
                'stripe_status' => 'canceled',
                'ends_at' => now(),
            ]);

            // Dispatch an event to notify that the subscription has been canceled
            Event::dispatch(new \App\Events\SubscriptionExpired($user));

            \Log::info('Apple Renewal Status Change Event processed', $data);
        } else {
            \Log::warning('User not found for Apple Renewal Status Change Event', $data);
        }
    }

    /**
     * Handle Apple SUBSCRIBED and DID_RENEW events.
     *
     * @param  array  $data
     * @return void
     */
    protected function handleAppleSubscribed(array $data)
    {
        // Extract relevant data from the $data array
        $productId = $data['productId'] ?? null;
        $transactionId = $data['transactionId'] ?? null;
        $originalTransactionId = $data['originalTransactionId'] ?? null;
        $purchaseDate = $data['purchaseDate'] ?? null;
        $subscriptionStatus = $data['subscriptionStatus'] ?? 'active'; // Default to active

        // Find the user by some unique identifier (e.g., email or user ID)
        // You'll need to adjust this based on your application's logic
        $user = User::where('appstore_transaction_id', $originalTransactionId)->first();

        if ($user) {
            // Update the user's subscription status in the database
            $user->subscriptions()->update([
                'stripe_status' => $subscriptionStatus,
                'ends_at' => now()->addYears(1), // Assuming a yearly subscription
            ]);

            // Dispatch events to notify other parts of the application
            if ($data['notificationType'] === 'SUBSCRIBED') {
                Event::dispatch(new SubscriptionSubscribed($user));
            } elseif ($data['notificationType'] === 'DID_RENEW') {
                Event::dispatch(new SubscriptionRenewed($user));
            }

            \Log::info('Apple Subscribed/Renewed Event processed', $data);
        } else {
            \Log::warning('User not found for Apple Subscribed/Renewed Event', $data);
        }
    }
}
