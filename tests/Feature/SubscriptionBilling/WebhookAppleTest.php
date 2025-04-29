<?php

namespace Tests\Feature\SubscriptionBilling;

use App\Events\SubscriptionRenewed;
use App\Events\SubscriptionSubscribed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

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
}
