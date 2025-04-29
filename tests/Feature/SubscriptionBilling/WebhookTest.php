<?php

namespace Tests\Feature\SubscriptionBilling;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Modules\SubscriptionBilling\Models\Subscription;
use Illuminate\Support\Facades\Event;
use Laravel\Cashier\Events\SubscriptionRenewed;
use Stripe\Subscription as StripeSubscription;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test customer.subscription.updated event when trial ends.
     *
     * @return void
     */
    public function testCustomerSubscriptionUpdatedTrialEnded()
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'stripe_status' => 'trialing',
            'trial_ends_at' => now()->addDays(7),
            'ends_at' => null,
        ]);

        $payload = [
            'type' => 'customer.subscription.updated',
            'data' => [
                'object' => [
                    'id' => $subscription->stripe_id,
                    'status' => 'active',
                    'trial_end' => null,
                ],
            ],
        ];

        $this->postJson('/api/webhooks/stripe', $payload)
            ->assertStatus(200);

        $subscription->refresh();

        $this->assertEquals('active', $subscription->stripe_status);
        $this->assertNull($subscription->trial_ends_at);
        $this->assertNull($subscription->ends_at);
    }

    /**
     * Test invoice.payment_succeeded event (Renewal).
     *
     * @return void
     */
    public function testInvoicePaymentSucceeded()
    {
        Event::fake();

        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'stripe_status' => 'active',
            'ends_at' => now()->addDays(30),
        ]);

        $payload = [
            'type' => 'invoice.payment_succeeded',
            'data' => [
                'object' => [
                    'subscription' => $subscription->stripe_id,
                    'lines' => [
                        'data' => [
                            [
                                'period' => [
                                    'end' => time() + (30 * 24 * 60 * 60), // 30 days from now
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->postJson('/api/webhooks/stripe', $payload)
            ->assertStatus(200);

        $subscription->refresh();

        $this->assertNotNull($subscription->ends_at);
        Event::assertDispatched(SubscriptionRenewed::class);
    }

    /**
     * Test invoice.payment_failed event.
     *
     * @return void
     */
    public function testInvoicePaymentFailed()
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'stripe_status' => 'active',
            'ends_at' => now()->addDays(30),
        ]);

        $payload = [
            'type' => 'invoice.payment_failed',
            'data' => [
                'object' => [
                    'subscription' => $subscription->stripe_id,
                ],
            ],
        ];

        $this->postJson('/api/webhooks/stripe', $payload)
            ->assertStatus(200);

        $subscription->refresh();

        $this->assertEquals('past_due', $subscription->stripe_status);
    }

    /**
     * Test customer.subscription.updated event when cancelled.
     *
     * @return void
     */
    public function testCustomerSubscriptionUpdatedCancel()
    {
        Event::fake();

        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'stripe_status' => 'active',
            'ends_at' => now()->addDays(30),
        ]);

        $payload = [
            'type' => 'customer.subscription.updated',
            'data' => [
                'object' => [
                    'id' => $subscription->stripe_id,
                    'cancel_at_period_end' => true,
                    'current_period_end' => time() + (10 * 24 * 60 * 60), // 10 days from now
                ],
            ],
        ];

        $this->postJson('/api/webhooks/stripe', $payload)
            ->assertStatus(200);

        $subscription->refresh();

        $this->assertEquals('canceled', $subscription->stripe_status);
        $this->assertNotNull($subscription->ends_at);
        Event::assertDispatched(\Laravel\Cashier\Events\SubscriptionCanceled::class);
    }

    /**
     * Test customer.subscription.deleted event.
     *
     * @return void
     */
    public function testCustomerSubscriptionDeleted()
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'stripe_status' => 'active',
            'ends_at' => now()->addDays(30),
        ]);

        $payload = [
            'type' => 'customer.subscription.deleted',
            'data' => [
                'object' => [
                    'id' => $subscription->stripe_id,
                ],
            ],
        ];

        $this->postJson('/api/webhooks/stripe', $payload)
            ->assertStatus(200);

        $subscription->refresh();

        $this->assertEquals('canceled', $subscription->stripe_status);
        $this->assertEquals(now()->format('Y-m-d H:i:s'), $subscription->ends_at->format('Y-m-d H:i:s'));
    }

    /**
     * Test Apple DID_CHANGE_RENEWAL_STATUS event when autoRenewStatus is false.
     *
     * @return void
     */
    public function testAppleDidChangeRenewalStatusAutoRenewStatusOff()
    {
        Event::fake();

        $user = User::factory()->create();
        $user->appstore_transaction_id = 'test_transaction_id';
        $user->save();

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'stripe_status' => 'active',
            'ends_at' => now()->addDays(30),
        ]);

        $payload = [
            'notificationType' => 'DID_CHANGE_RENEWAL_STATUS',
            'autoRenewStatus' => false,
            'originalTransactionId' => 'test_transaction_id',
        ];

        $this->postJson('/api/webhooks/apple', $payload)
            ->assertStatus(200);

        $subscription->refresh();

        $this->assertEquals('canceled', $subscription->stripe_status);
        $this->assertEquals(now()->format('Y-m-d H:i:s'), $subscription->ends_at->format('Y-m-d H:i:s'));
        Event::assertDispatched(\App\Events\SubscriptionExpired::class);
    }

    /**
     * Test Google Pub/Sub message decoding and parsing.
     *
     * @return void
     */
    public function testGooglePubSubMessageDecodingAndParsing()
    {
        $payload = [
            'message' => [
                'data' => base64_encode(json_encode([
                    'subscription' => 'projects/your-project/subscriptions/your-subscription',
                    'message' => [
                        'messageId' => '12345',
                        'publishTime' => '2023-10-27T12:00:00.000Z',
                        'attributes' => [
                            'key' => 'value',
                        ],
                        'data' => base64_encode(json_encode([
                            'userId' => 1,
                            'productId' => 'premium',
                            'purchaseToken' => 'test_token',
                        ])),
                    ],
                ])),
            ],
        ];

        $this->postJson('/api/webhooks/google', $payload)
            ->assertStatus(200);
    }
}
