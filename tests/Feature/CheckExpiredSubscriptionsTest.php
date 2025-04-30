<?php

namespace Tests\Feature;

use App\Modules\SubscriptionBilling\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckExpiredSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the command finds past canceled subscriptions and sets status to expired.
     */
    public function test_command_finds_past_canceled_subscriptions_and_sets_status_to_expired(): void
    {
        // Create a subscription with canceled status and ends_at in the past
        $subscription = Subscription::factory()->create([
            'status' => 'canceled',
            'ends_at' => now()->subDay(),
        ]);

        // Run the command
        $this->artisan('subscriptions:check-expired');

        // Assert that the subscription status is updated to expired
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'expired',
        ]);
    }
}
