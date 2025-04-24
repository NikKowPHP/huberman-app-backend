<?php

namespace Tests\Unit;

use App\Modules\SubscriptionBilling\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_attributes()
    {
        $plan = Plan::factory()->create([
            'name' => 'Basic',
            'slug' => 'basic',
            'description' => 'Basic plan',
            'price' => 10,
            'interval' => 'month',
            'interval_count' => 1,
            'trial_period_days' => 7,
            'is_active' => true,
        ]);

        $this->assertEquals('Basic', $plan->name);
        $this->assertEquals('basic', $plan->slug);
        $this->assertEquals('Basic plan', $plan->description);
        $this->assertEquals(10, $plan->price);
        $this->assertEquals('month', $plan->interval);
        $this->assertEquals(1, $plan->interval_count);
        $this->assertEquals(7, $plan->trial_period_days);
        $this->assertTrue($plan->is_active);
    }

    /** @test */
    public function it_can_be_created_using_factory()
    {
        $plan = Plan::factory()->create();

        $this->assertInstanceOf(Plan::class, $plan);
    }

    /** @test */
    public function it_has_is_active_scope()
    {
        Plan::factory()->create(['is_active' => true]);
        Plan::factory()->create(['is_active' => false]);

        $activePlans = Plan::active()->get();

        $this->assertEquals(1, $activePlans->count());
        $this->assertTrue($activePlans->first()->is_active);
    }
}
