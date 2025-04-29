<?php

namespace Tests\Unit\SubscriptionBilling;

use App\Events\SubscriptionExpired;
use App\Listeners\SubscriptionExpiredListener;
use App\Modules\SubscriptionBilling\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SubscriptionExpiredTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_expired_listener_updates_subscription_status()
    {
        // Arrange
        Event::fake();

        $subscription = Subscription::factory()->create([
            'status' => 'active',
        ]);

        // Act
        event(new SubscriptionExpired($subscription));

        // Assert
        $subscription->refresh();
        $this->assertEquals('expired', $subscription->getAttribute('status'));

        Event::assertDispatched(SubscriptionExpired::class, function ($e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });
    }
}
