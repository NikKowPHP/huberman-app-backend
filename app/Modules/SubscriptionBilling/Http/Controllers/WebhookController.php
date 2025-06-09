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
use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface; 
use Illuminate\Support\Facades\Log;

class WebhookController extends CashierController
{
    private $appleSubscriptionService;
    private $subscriptionService; 

   public function __construct(
        AppleSubscriptionService $appleSubscriptionService,
        SubscriptionServiceInterface $subscriptionService // Use interface
    )
    {
        $this->appleSubscriptionService = $appleSubscriptionService;
        $this->subscriptionService = $subscriptionService; // Assign service
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
            $jws = $request->input('signedPayload', $request->getContent()); // Handle potential form encoding too
             if (empty($jws)) {
                  throw new \InvalidArgumentException('Missing signedPayload or request body.');
             }

            // Decode and verify (assuming this method exists and works)
            $decodedPayload = $this->appleSubscriptionService->decodeAndVerifyJWS($jws);
            $notificationType = $decodedPayload['notificationType'] ?? null;
            $payloadData = isset($decodedPayload['data']) ? (object)$decodedPayload['data'] : null; // Ensure data is object

             if (!$notificationType || !$payloadData) {
                  Log::error('Invalid Apple Webhook payload structure after decoding.', ['decoded' => $decodedPayload]);
                  return response('Invalid Apple Webhook payload structure', 400);
             }

            Log::info('Processing Apple Notification:', ['type' => $notificationType]);

            // Route based on notification type
            switch ($notificationType) {
                case 'SUBSCRIBED':
                case 'DID_RENEW':
                    // Assuming handleAppleSubscribed exists and handles both
                    $this->handleAppleSubscribed($decodedPayload); // Pass full decoded payload if needed
                    break;
                case 'DID_CHANGE_RENEWAL_STATUS':
                     // Let AppleSubscriptionService handle this complex one for now
                    $this->appleSubscriptionService->handleDidChangeRenewalStatus($jws); // Pass original JWS if needed
                    break;
                 case 'DID_FAIL_TO_RENEW':
                      // Call handler in SubscriptionService
                     $this->subscriptionService->handleAppleRenewalFailed($payloadData);
                      break;
                 case 'EXPIRED': // **** ADDED CASE ****
                      // Call handler in SubscriptionService
                     $this->subscriptionService->handleAppleExpired($payloadData);
                      break;
                case 'GRACE_PERIOD_EXPIRED':
                    $this->subscriptionService->handleAppleGracePeriodExpired($payloadData);
                    break;
                case 'REVOKED':
                    $this->subscriptionService->handleAppleRevoked($payloadData);
                    break;
                default:
                    Log::info('Received unhandled Apple notification type: ' . $notificationType);
                    break;
            }

            return response('Apple Webhook Received', 200);
        } catch (\Exception $e) {
            Log::error('Apple Webhook Error: ' . $e->getMessage(), [
                 'trace' => $e->getTraceAsString() // Add trace for debugging
            ]);
            // Return 500 for unexpected errors so Apple might retry
            return response('Error processing Apple Webhook: ' . $e->getMessage(), 500);
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

    /**
     * Handle a Google Pub/Sub webhook.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleGoogleWebhook(Request $request)
    {
        try {
            $payload = json_decode($request->getContent(), true);

            if (!isset($payload['message']['data'])) {
                \Log::error('Missing data in Google Pub/Sub message', $payload);
                return response('Invalid Google Pub/Sub message', 400);
            }

            $data = base64_decode($payload['message']['data']);
            $message = json_decode($data, true);

            if (config('services.google_play.mock_validation')) {
                // Mock validation logic
                \Log::info('Google Play Purchase (Mock Validated)', $message);
                // TODO: Process the message with mock data
            } else {
                $packageName = config('services.google_play.package_name');

                // TODO: Initialize Google Play Developer API client
                $client = new \Google_Client();
                $client->setApplicationName('Huberman App');
                $client->setScopes([\Google_Service_AndroidPublisher::ANDROIDPUBLISHER]);
                $client->setAuthConfig(storage_path('app/google-play-credentials.json')); // Path to your service account credentials JSON file

                $androidPublisher = new \Google_Service_AndroidPublisher($client);

                // TODO: Validate the purchase
                try {
                    $purchase = $androidPublisher->purchases_subscriptions->get(
                        $packageName,
                        $message['subscriptionId'],
                        $message['purchaseToken']
                    );

                    \Log::info('Google Play Purchase Validated', $purchase);

                    // TODO: Acknowledge the purchase
                    $acknowledgeResult = $androidPublisher->purchases_subscriptions->acknowledge(
                        $packageName,
                        $message['subscriptionId'],
                        $message['purchaseToken'],
                        new \Google_Service_AndroidPublisher_SubscriptionPurchasesAcknowledgeRequest([
                            'developerPayload' => 'Acknowledged'
                        ])
                    );

                    \Log::info('Google Play Purchase Acknowledged', $acknowledgeResult);

                } catch (\Exception $e) {
                    \Log::error('Google Play Purchase Validation Error: ' . $e->getMessage());
                    return response('Invalid Google Pub/Sub Webhook', 400);
                }

                \Log::info('Google Pub/Sub Message (Mock Processed)', $message);
                // TODO: Implement actual validation and acknowledgement logic
                // based on the message type (e.g., SUBSCRIPTION_PURCHASED,
                // SUBSCRIPTION_RENEWED, SUBSCRIPTION_CANCELED, etc.)
                if ($message['message']['attributes']['subscriptionNotificationType'] === 'SUBSCRIPTION_PURCHASED' || $message['message']['attributes']['subscriptionNotificationType'] === 'SUBSCRIPTION_RENEWED') {
                    $userId = $message['message']['data']['userId'] ?? null;
                    $productId = $message['message']['data']['productId'] ?? null;
                    $purchaseToken = $message['message']['data']['purchaseToken'] ?? null;

                    if (!$userId || !$productId || !$purchaseToken) {
                        \Log::warning('Missing data in Google Pub/Sub message', $message);
                        return response('Invalid Google Pub/Sub message', 400);
                    }

                    $user = User::find($userId);

                    if ($user) {
                        $subscription = $user->subscriptions()->create([
                            'name' => 'default',
                            'google_play_product_id' => $productId,
                            'google_play_purchase_token' => $purchaseToken,
                            'stripe_status' => 'active', // Assuming successful purchase
                            'ends_at' => now()->addYears(1), // Assuming yearly subscription
                        ]);

                        Event::dispatch(new SubscriptionSubscribed($user));

                        // Acknowledge the purchase
                        $acknowledgeResult = $androidPublisher->purchases_subscriptions->acknowledge(
                            $packageName,
                            $message['subscriptionId'],
                            $message['purchaseToken'],
                            new \Google_Service_AndroidPublisher_SubscriptionPurchasesAcknowledgeRequest([
                                'developerPayload' => 'Acknowledged'
                            ])
                        );

                        \Log::info('Google Play Purchase Acknowledged', $acknowledgeResult);
                    } else {
                        \Log::warning('User not found for Google Pub/Sub message', $message);
                    }
                }
            }

            return response('Google Pub/Sub Webhook Received', 200);
        } catch (\Exception $e) {
            \Log::error('Google Pub/Sub Webhook Error: ' . $e->getMessage());
            return response('Invalid Google Pub/Sub Webhook', 400);
        }
    }
}
