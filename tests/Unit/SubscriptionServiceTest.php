<?php

namespace Tests\Unit;

use App\Modules\SubscriptionBilling\Services\SubscriptionService;
use App\Modules\SubscriptionBilling\Models\Plan;
use App\Modules\SubscriptionBilling\Models\Subscription;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_false_when_user_has_no_subscription()
    {
        $user = User::factory()->create();
        $service = new SubscriptionService();

        $this->assertFalse($service->userHasActivePremiumSubscription($user));
    }

    /** @test */
    public function it_returns_false_when_user_has_free_plan()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['name' => 'Free']);
        Subscription::factory()->create(['user_id' => $user->id, 'plan_id' => $plan->id, 'status' => 'active']);
        $service = new SubscriptionService();

        $this->assertFalse($service->userHasActivePremiumSubscription($user));
    }

    /** @test */
    public function it_returns_true_when_user_has_active_premium_plan()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['name' => 'Premium']);
        Subscription::factory()->create(['user_id' => $user->id, 'plan_id' => $plan->id, 'status' => 'active']);
        $service = new SubscriptionService();

        $this->assertTrue($service->userHasActivePremiumSubscription($user));
    }

    /** @test */
    public function it_returns_true_when_user_has_trialing_premium_plan()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['name' => 'Premium']);
        Subscription::factory()->create(['user_id' => $user->id, 'plan_id' => $plan->id, 'status' => 'trialing']);
        $service = new SubscriptionService();

        $this->assertTrue($service->userHasActivePremiumSubscription($user));
    }

    /** @test */
    public function it_returns_false_when_user_has_canceled_premium_plan()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['name' => 'Premium']);
        Subscription::factory()->create(['user_id' => $user->id, 'plan_id' => $plan->id, 'status' => 'canceled']);
        $service = new SubscriptionService();

        $this->assertFalse($service->userHasActivePremiumSubscription($user));
    }

    /** @test */
    public function it_returns_false_when_user_has_expired_premium_plan()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['name' => 'Premium']);
        Subscription::factory()->create(['user_id' => $user->id, 'plan_id' => $plan->id, 'status' => 'expired']);
        $service = new SubscriptionService();

        $this->assertFalse($service->userHasActivePremiumSubscription($user));
    }

    /** @test */
    public function it_returns_false_when_user_has_past_due_premium_plan()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['name' => 'Premium']);
        Subscription::factory()->create(['user_id' => $user->id, 'plan_id' => $plan->id, 'status' => 'past_due']);
        $service = new SubscriptionService();

        $this->assertFalse($service->userHasActivePremiumSubscription($user));
    }

    /** @test */
    public function it_caches_the_subscription_status()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['name' => 'Premium']);
        Subscription::factory()->create(['user_id' => $user->id, 'plan_id' => $plan->id, 'status' => 'active']);
        $service = new SubscriptionService();

        $this->assertTrue($service->userHasActivePremiumSubscription($user));
        \Cache::shouldReceive('tags')->once()->with(['user:' . $user->id])->andReturnSelf();
        \Cache::shouldReceive('remember')->once()->with('premium_subscription', \Carbon\CarbonInterval::minutes(60), \Closure::class)->andReturn(true);

        $service->userHasActivePremiumSubscription($user);
    }

    /** @test */
    public function it_clears_the_cache_when_subscription_is_updated()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['name' => 'Premium']);
        $subscription = Subscription::factory()->create(['user_id' => $user->id, 'plan_id' => $plan->id, 'status' => 'active']);
        $service = new SubscriptionService();

        \Cache::shouldReceive('tags')->once()->with(['user:' . $user->id])->andReturnSelf();
        \Cache::shouldReceive('flush')->once();

        $subscription->status = 'canceled';
        $subscription->save();
    }
}
