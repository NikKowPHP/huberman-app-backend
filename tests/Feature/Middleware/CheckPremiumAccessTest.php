<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\CheckPremiumAccess;
use App\Modules\SubscriptionBilling\Models\Plan;
use App\Modules\SubscriptionBilling\Models\Subscription;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\Feature\ApiTestCase;

class CheckPremiumAccessTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['api', 'auth:sanctum', CheckPremiumAccess::class])->get('/test-premium-route', function () {
            return response()->json(['message' => 'Success']);
        });
    }

    /** @test */
    public function it_allows_access_for_premium_users()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['name' => 'Premium']);
        Subscription::factory()->create(['user_id' => $user->id, 'plan_id' => $plan->id, 'status' => 'active']);

        $response = $this->actingAs($user)->getJson('/test-premium-route');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_denies_access_for_free_users()
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['name' => 'Free']);
        Subscription::factory()->create(['user_id' => $user->id, 'plan_id' => $plan->id, 'status' => 'active']);

        $response = $this->actingAs($user)->getJson('/test-premium-route');

        $response->assertStatus(403);
    }

    /** @test */
    public function it_denies_access_for_users_without_subscription()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/test-premium-route');

        $response->assertStatus(403);
    }

    /** @test */
    public function it_denies_access_for_unauthenticated_users()
    {
        $response = $this->getJson('/test-premium-route');

        $response->assertStatus(401);
    }
}
