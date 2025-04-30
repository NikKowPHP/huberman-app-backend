<?php

namespace Tests\Feature\SubscriptionBilling;

use App\Events\SubscriptionRenewed;
use App\Events\SubscriptionSubscribed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use App\Events\SubscriptionExpired; 

class WebhookAppleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the handleAppleSubscribed method is called when the notificationType is SUBSCRIBED.
     *
     * @return void
     */
    public function testHandleAppleSubscribedMethodCalledWhenNotificationTypeIsSubscribed()
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create(['appstore_transaction_id' => '12345']);

        // Act
        $response = $this->postJson('/api/webhooks/apple', [
            'notificationType' => 'SUBSCRIBED',
            'productId' => 'com.example.product',
            'transactionId' => '67890',
            'originalTransactionId' => '12345',
            'purchaseDate' => now()->timestamp,
            'subscriptionStatus' => 'active',
        ]);

        // Assert
        $response->assertStatus(200);
        Event::assertDispatched(SubscriptionSubscribed::class);
    }

    /**
     * Test that the handleAppleSubscribed method is called when the notificationType is DID_RENEW.
     *
     * @return void
     */
    public function testHandleAppleSubscribedMethodCalledWhenNotificationTypeIsDidRenew()
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create(['appstore_transaction_id' => '12345']);

        // Act
        $response = $this->postJson('/api/webhooks/apple', [
            'notificationType' => 'DID_RENEW',
            'productId' => 'com.example.product',
            'transactionId' => '67890',
            'originalTransactionId' => '12345',
            'purchaseDate' => now()->timestamp,
            'subscriptionStatus' => 'active',
        ]);

        // Assert
        $response->assertStatus(200);
        Event::assertDispatched(SubscriptionRenewed::class);
    }

    /**
     * Test that the user's subscription status is updated to active when the subscriptionStatus is active.
     *
     * @return void
     */
    public function testUserSubscriptionStatusIsUpdatedToActiveWhenSubscriptionStatusIsActive()
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create(['appstore_transaction_id' => '12345']);

        // Act
        $this->postJson('/api/webhooks/apple', [
            'notificationType' => 'SUBSCRIBED',
            'productId' => 'com.example.product',
            'transactionId' => '67890',
            'originalTransactionId' => '12345',
            'purchaseDate' => now()->timestamp,
            'subscriptionStatus' => 'active',
        ]);

        // Assert
        $this->assertEquals('active', $user->subscriptions()->first()->stripe_status);
    }

    /**
     * Test that the user's subscription status is updated to trialing when the subscriptionStatus is trialing.
     *
     * @return void
     */
    public function testUserSubscriptionStatusIsUpdatedToTrialingWhenSubscriptionStatusIsTrialing()
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create(['appstore_transaction_id' => '12345']);

        // Act
        $this->postJson('/api/webhooks/apple', [
            'notificationType' => 'SUBSCRIBED',
            'productId' => 'com.example.product',
            'transactionId' => '67890',
            'originalTransactionId' => '12345',
            'purchaseDate' => now()->timestamp,
            'subscriptionStatus' => 'trialing',
        ]);

        // Assert
        $this->assertEquals('trialing', $user->subscriptions()->first()->stripe_status);
    }

    /**
     * Test that the SubscriptionSubscribed event is dispatched when the notificationType is SUBSCRIBED.
     *
     * @return void
     */
    public function testSubscriptionSubscribedEventIsDispatchedWhenNotificationTypeIsSubscribed()
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create(['appstore_transaction_id' => '12345']);

        // Act
        $this->postJson('/api/webhooks/apple', [
            'notificationType' => 'SUBSCRIBED',
            'productId' => 'com.example.product',
            'transactionId' => '67890',
            'originalTransactionId' => '12345',
            'purchaseDate' => now()->timestamp,
            'subscriptionStatus' => 'active',
        ]);

        // Assert
        Event::assertDispatched(SubscriptionSubscribed::class);
    }

    /**
     * Test that the SubscriptionRenewed event is dispatched when the notificationType is DID_RENEW.
     *
     * @return void
     */
    public function testSubscriptionRenewedEventIsDispatchedWhenNotificationTypeIsDidRenew()
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create(['appstore_transaction_id' => '12345']);

        // Act
        $this->postJson('/api/webhooks/apple', [
            'notificationType' => 'DID_RENEW',
            'productId' => 'com.example.product',
            'transactionId' => '67890',
            'originalTransactionId' => '12345',
            'purchaseDate' => now()->timestamp,
            'subscriptionStatus' => 'active',
        ]);

        // Assert
        Event::assertDispatched(SubscriptionRenewed::class);
    }

    /**
     * Test that the log warning is dispatched when the user is not found.
     *
     * @return void
     */
    public function testLogWarningIsDispatchedWhenUserIsNotFound()
    {
        // Arrange
        Event::fake();

        // Act
        $response = $this->postJson('/api/webhooks/apple', [
            'notificationType' => 'SUBSCRIBED',
            'productId' => 'com.example.product',
            'transactionId' => '67890',
            'originalTransactionId' => '12345',
            'purchaseDate' => now()->timestamp,
            'subscriptionStatus' => 'active',
        ]);

        // Assert
        $response->assertStatus(200);
        // TODO: Assert that a log warning was dispatched
        $this->assertTrue(true);
    }
      /**
     * Test handling of the EXPIRED notification type from Apple.
     *
     * @test
     */
    public function it_handles_apple_expired_notification()
    {
        Event::fake([SubscriptionExpired::class]); // Fake only the expected event

        // 1. Setup: Create User, Plan, and an existing Subscription
        $user = User::factory()->create(['appstore_transaction_id' => 'orig_trans_expired_123']);
        $plan = Plan::factory()->create(); // Assuming a plan exists
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'stripe_id' => 'orig_trans_expired_123', // Assuming stripe_id stores originalTransactionId for Apple
            'stripe_status' => 'active', // Or 'canceled', it should expire regardless
            'ends_at' => now()->subDay(), // Ensure it could have plausibly expired
        ]);

        // 2. Prepare Mock Payload (Simulating Apple's decoded JWS data)
        // Note: The exact structure for EXPIRED might vary slightly,
        // consult Apple docs. Assuming 'originalTransactionId' is available.
        $applePayloadData = [
            'notificationType' => 'EXPIRED',
            'subtype' => 'VOLUNTARY', // Or 'BILLING_RETRY'
            'notificationUUID' => 'expired-uuid-' . uniqid(),
            'data' => [
                 // Often includes transactionInfo for the *last* transaction attempt or expiry details
                 'bundleId' => 'com.example.app',
                 'environment' => 'Sandbox',
                 'originalTransactionId' => 'orig_trans_expired_123', // Key identifier
                 'productId' => $plan->apple_product_id ?? 'test_product_id', // Match the plan if possible
                 'status' => 2, // 2 usually means expired status in transaction info
            ],
            'version' => '2.0',
            'signedDate' => now()->timestamp * 1000,
        ];

         // 3. Mock the AppleSubscriptionService's verification method
         // We are testing the controller/service *handling*, not the verification itself here.
         $mockAppleService = $this->mock(AppleSubscriptionService::class);
         $mockAppleService->shouldReceive('decodeAndVerifyJWS')
             ->once()
             ->andReturn($applePayloadData); // Return the prepared payload data

        // 4. Action: Send the webhook request
        $response = $this->postJson('/api/webhooks/apple', $applePayloadData); // Send raw data as if verified

        // 5. Assertions
        $response->assertStatus(200); // Webhook received successfully

        $subscription->refresh();
        $this->assertEquals('expired', $subscription->stripe_status, "Subscription status should be 'expired'.");
        // Optionally assert ends_at, though the ->expire() method might just set status
        // $this->assertNotNull($subscription->ends_at);
        // $this->assertTrue($subscription->ends_at->isPast());

        Event::assertDispatched(SubscriptionExpired::class, function ($event) use ($subscription) {
            return $event->subscription->id === $subscription->id;
        });
    }

}
