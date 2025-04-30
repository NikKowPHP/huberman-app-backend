<?php

namespace Tests\Unit;

use App\Events\SubscriptionCanceled;
use App\Events\SubscriptionExpired;
use App\Events\SubscriptionRenewed;
use App\Events\SubscriptionStarted;
use App\Listeners\ClearUserEntitlementCache;
use App\Modules\SubscriptionBilling\Models\Subscription;
use App\Modules\UserManagement\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ClearUserEntitlementCacheTest extends TestCase
{
    /** @test */
    public function it_clears_the_user_entitlement_cache_when_a_subscription_event_is_dispatched()
    {
        Cache::shouldReceive('tags')
            ->with("user:1")
            ->times(4)
            ->andReturnSelf();

        Cache::shouldReceive('flush')
            ->times(4);

        $subscription = new Subscription(['user_id' => 1]);
        $user = new User(['id' => 1]);

        $listener = new ClearUserEntitlementCache();
        $listener->handle(new SubscriptionStarted($subscription));
        $listener->handle(new SubscriptionRenewed($subscription));
        $listener->handle(new SubscriptionCanceled($subscription));
        $listener->handle(new SubscriptionExpired($user));
    }
}
