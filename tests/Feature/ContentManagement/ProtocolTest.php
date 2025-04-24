<?php

namespace Tests\Feature\ContentManagement;

use App\Modules\ContentManagement\Models\Protocol;
use App\Modules\SubscriptionBilling\Models\Plan;
use App\Modules\SubscriptionBilling\Models\Subscription;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ApiTestCase;

class ProtocolTest extends ApiTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_protocols()
    {
        Protocol::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/protocols');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'episodes',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_show_a_protocol()
    {
        $protocol = Protocol::factory()->create();

        $response = $this->getJson('/api/v1/protocols/' . $protocol->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'episodes',
            ]);
    }

    /** @test */
    public function it_conditionally_includes_implementation_guide_for_premium_users()
    {
        // Ensure plans exist
        $this->seed(DatabaseSeeder::class);

        $protocol = Protocol::factory()->create([
            'implementation_guide' => 'This is the premium implementation guide.',
        ]);

        // Test with a free user
        $freeUser = User::factory()->create();
        $freePlan = Plan::where('type', 'free')->first();
        Subscription::factory()->create([
            'user_id' => $freeUser->id,
            'plan_id' => $freePlan->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($freeUser, 'sanctum')
                         ->getJson('/api/v1/protocols/' . $protocol->id);

        $response->assertStatus(200)
                 ->assertJsonMissing(['implementation_guide']);

        // Test with a premium user
        $premiumUser = User::factory()->create();
        $premiumPlan = Plan::where('type', 'premium')->first();
        Subscription::factory()->create([
            'user_id' => $premiumUser->id,
            'plan_id' => $premiumPlan->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($premiumUser, 'sanctum')
                         ->getJson('/api/v1/protocols/' . $protocol->id);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'name',
                     'description',
                     'episodes',
                     'implementation_guide',
                 ])
                 ->assertJsonFragment(['implementation_guide' => 'This is the premium implementation guide.']);
    }

    /** @test */
    public function it_conditionally_loads_protocols_based_on_premium_access()
    {
        // Ensure plans exist
        $this->seed(DatabaseSeeder::class);

        // Create some protocols, some with implementation guides
        Protocol::factory()->count(3)->create();
        Protocol::factory()->create(['implementation_guide' => 'Premium Guide 1']);
        Protocol::factory()->create(['implementation_guide' => 'Premium Guide 2']);

        // Test with a free user
        $freeUser = User::factory()->create();
        $freePlan = Plan::where('type', 'free')->first();
        Subscription::factory()->create([
            'user_id' => $freeUser->id,
            'plan_id' => $freePlan->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($freeUser, 'sanctum')
                         ->getJson('/api/v1/protocols');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'description',
                             'episodes',
                         ],
                     ],
                 ]);

        // Ensure no implementation guides are loaded for free user
        foreach (json_decode($response->getContent(), true)['data'] as $protocol) {
            $this->assertArrayNotHasKey('implementation_guide', $protocol);
        }

        // Test with a premium user
        $premiumUser = User::factory()->create();
        $premiumPlan = Plan::where('type', 'premium')->first();
        Subscription::factory()->create([
            'user_id' => $premiumUser->id,
            'plan_id' => $premiumPlan->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($premiumUser, 'sanctum')
                         ->getJson('/api/v1/protocols');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'description',
                             'episodes',
                             'implementation_guide',
                         ],
                     ],
                 ]);

        // Ensure implementation guides are loaded for premium user
        foreach (json_decode($response->getContent(), true)['data'] as $protocol) {
            if (isset($protocol['implementation_guide'])) {
                $this->assertNotEmpty($protocol['implementation_guide']);
            }
        }
    }
}
