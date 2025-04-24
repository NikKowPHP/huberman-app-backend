<?php

namespace Tests\Feature\SubscriptionBilling;

use App\Modules\SubscriptionBilling\Models\Plan;
use App\Modules\SubscriptionBilling\Models\Subscription;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_null_when_user_has_no_subscription()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/user/subscription');

        $response->assertStatus(200)
            ->assertJsonMissing(['id']);
    }

    /** @test */
    public function it_returns_the_users_active_subscription()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'stripe_status' => 'active',
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/user/subscription');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $subscription->id,
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'stripe_status' => 'active',
            ]);
    }
}
