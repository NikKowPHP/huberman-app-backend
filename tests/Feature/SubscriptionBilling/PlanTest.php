<?php

namespace Tests\Feature\SubscriptionBilling;

use App\Modules\SubscriptionBilling\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_a_list_of_plans()
    {
        Plan::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/plans');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }
}
