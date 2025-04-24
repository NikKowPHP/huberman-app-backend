<?php

namespace Tests\Feature\SubscriptionBilling;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Cashier\Events\SubscriptionStarted;
use Tests\TestCase;

class WebhookCheckoutSessionCompletedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_handles_checkout_session_completed_webhook()
    {
        // Arrange
        Event::fake();

        // Create a user
        $user = User::factory()->create(['stripe_id' => 'cus_xxxxxxxxxxxxxxxxx']);

        // Set the STRIPE_WEBHOOK_SECRET environment variable
        $webhookSecret = 'whsec_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
        config(['cashier.webhook.secret' => $webhookSecret]);
        putenv('STRIPE_WEBHOOK_SECRET=' . $webhookSecret);

        // Simulate the checkout.session.completed webhook
        $payload = [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'customer' => 'cus_xxxxxxxxxxxxxxxxx', // Replace with a valid customer ID
                    'customer_email' => $user->email,
                    'subscription' => 'sub_xxxxxxxxxxxxxxxxx', // Replace with a valid subscription ID
                    'mode' => 'subscription',
                    'payment_status' => 'paid',
                    'line_items' => [
                        'data' => [
                            [
                                'price' => [
                                    'id' => 'price_xxxxxxxxxxxxxxxxx', // Replace with a valid price ID
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // Generate the signature
        $signature = hash_hmac('sha256', json_encode($payload), $webhookSecret);
        $headers = ['Stripe-Signature' => 't=' . time() . ',v1=' . $signature];

        // Act
        $response = $this->postJson('/api/webhooks/stripe', $payload, $headers);

        // Assert
        $response->assertStatus(200);

        // Assert that a Subscription is created
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'stripe_id' => 'sub_xxxxxxxxxxxxxxxxx',
            'stripe_customer_id' => 'cus_xxxxxxxxxxxxxxxxx',
            'name' => 'default',
            'stripe_status' => 'trialing', // Or 'active' based on the plan
        ]);

        // Assert that ends_at and trial_ends_at are set correctly
        $subscription = Subscription::where('user_id', $user->id)->first();
        $this->assertNotNull($subscription->ends_at);
        $this->assertNotNull($subscription->trial_ends_at);

        // Assert that the SubscriptionStarted event is dispatched
        Event::assertDispatched(SubscriptionStarted::class, function ($event) use ($subscription) {
            return $event->subscription->id === $subscription->id;
        });

        // Assert that the User.stripe_id is updated
        $this->assertEquals('cus_xxxxxxxxxxxxxxxxx', $user->fresh()->stripe_id);
    }
}
