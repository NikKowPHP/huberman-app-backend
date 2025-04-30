<?php

namespace Tests\Feature\TrackingService;

use App\Modules\TrackingService\Models\TrackingLog;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingLogAdherenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_adherence_endpoint_requires_authentication(): void
    {
        $response = $this->postJson('/api/tracking/log-adherence', []);

        $response->assertStatus(401);
    }

    public function test_log_adherence_endpoint_requires_premium_subscription(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/tracking/log-adherence', []);

        $response->assertStatus(403);
    }

    public function test_log_adherence_endpoint_creates_tracking_log_on_valid_request(): void
    {
        $user = User::factory()->create();
        $user->subscriptions()->create(['plan_id' => 1, 'ends_at' => now()->addDay()]);
        $this->actingAs($user);

        $data = ['action' => 'test_action', 'details' => 'test_details'];

        $response = $this->postJson('/api/tracking/log-adherence', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tracking_logs', [
            'user_id' => $user->id,
            'data' => json_encode($data),
        ]);
    }
}
