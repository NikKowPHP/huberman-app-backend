<?php

namespace Tests\Unit\Console\Commands;

use App\Modules\SubscriptionBilling\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckExpiredSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_expires_canceled_subscriptions_past_their_end_date()
    {
        // Create a subscription that should be expired
        $expiredSubscription = Subscription::factory()->create([
            'status' => 'canceled',
            'ends_at' => now()->subDay(),
        ]);

        // Create a subscription that shouldn't be expired yet
        $activeSubscription = Subscription::factory()->create([
            'status' => 'canceled',
            'ends_at' => now()->addDay(),
        ]);

        // Create a subscription with different status
        $otherStatusSubscription = Subscription::factory()->create([
            'status' => 'active',
            'ends_at' => now()->subDay(),
        ]);

        $this->artisan('subscriptions:check-expired')
            ->expectsOutput('Checked 1 subscriptions and updated their status to expired.')
            ->assertExitCode(0);

        $this->assertEquals('expired', $expiredSubscription->fresh()->status);
        $this->assertEquals('canceled', $activeSubscription->fresh()->status);
        $this->assertEquals('active', $otherStatusSubscription->fresh()->status);
    }

    /** @test */
    public function it_handles_case_with_no_expired_subscriptions()
    {
        Subscription::factory()->create([
            'status' => 'canceled',
            'ends_at' => now()->addDay(),
        ]);

        $this->artisan('subscriptions:check-expired')
            ->expectsOutput('Checked 0 subscriptions and updated their status to expired.')
            ->assertExitCode(0);
    }
}