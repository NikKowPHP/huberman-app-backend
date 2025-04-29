<?php

namespace Tests\Unit\SubscriptionBilling;

use App\Events\SubscriptionRenewalFailed;
use App\Listeners\SubscriptionRenewalFailedListener;
use App\Modules\SubscriptionBilling\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SubscriptionRenewalFailedTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_renewal_failed_listener_updates_subscription_status()
    {
        // Arrange
        Event::fake();

        $subscription = Subscription::factory()->create([
            'status' => 'active',
        ]);

        // Act
        event(new SubscriptionRenewalFailed($subscription));

        // Assert
        $subscription->refresh();
        $this->assertEquals('past_due', $subscription->getAttribute('status'));

        Event::assertDispatched(SubscriptionRenewalFailed::class, function ($e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });
    }
}
