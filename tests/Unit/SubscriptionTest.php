<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Event;
use App\Modules\SubscriptionBilling\Models\Plan;
use App\Modules\SubscriptionBilling\Models\Subscription;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_attributes()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'name' => 'default',
            'stripe_id' => 'stripe_id',
            'stripe_status' => 'active',
            'stripe_price' => 'stripe_price',
            'quantity' => 1,
            'trial_ends_at' => now()->addDays(7),
            'ends_at' => null,
        ]);

        $this->assertEquals($user->id, $subscription->user_id);
        $this->assertEquals($plan->id, $subscription->plan_id);
        $this->assertEquals('default', $subscription->name);
        $this->assertEquals('stripe_id', $subscription->stripe_id);
        $this->assertEquals('active', $subscription->stripe_status);
        $this->assertEquals('stripe_price', $subscription->stripe_price);
        $this->assertEquals(1, $subscription->quantity);
        $this->assertEquals(now()->addDays(7)->format('Y-m-d H:i:s'), $subscription->trial_ends_at->format('Y-m-d H:i:s'));
        $this->assertNull($subscription->ends_at);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $subscription->user);
    }

    /** @test */
    public function it_belongs_to_a_plan()
    {
        $plan = Plan::factory()->create();
        $subscription = Subscription::factory()->create(['plan_id' => $plan->id]);

        $this->assertInstanceOf(Plan::class, $subscription->plan);
    }

    /** @test */
    public function it_has_active_scope()
    {
        Subscription::factory()->create(['stripe_status' => 'active']);
        Subscription::factory()->create(['stripe_status' => 'canceled']);

        $activeSubscriptions = Subscription::active()->get();

        $this->assertEquals(1, $activeSubscriptions->count());
        $this->assertEquals('active', $activeSubscriptions->first()->stripe_status);
    }

    /** @test */
    public function it_has_trialing_scope()
    {
        Subscription::factory()->create(['trial_ends_at' => now()->addDays(7)]);
        Subscription::factory()->create(['trial_ends_at' => null]);

        $trialingSubscriptions = Subscription::trialing()->get();

        $this->assertEquals(1, $trialingSubscriptions->count());
        $this->assertNotNull($trialingSubscriptions->first()->trial_ends_at);
    }

    /** @test */
    public function it_can_expire_a_subscription()
    {
        Event::fake();

        $subscription = \App\Modules\SubscriptionBilling\Models\Subscription::factory()->create([
            'stripe_status' => 'active',
        ]);

        $subscription->expire();

        $this->assertEquals('expired', $subscription->fresh()->stripe_status);
        $this->assertEventDispatched(\App\Events\SubscriptionExpired::class, function ($event) {
            return $event->subscription->id === $subscription->id;
        });
    }
}
