<?php

namespace Tests\Feature\TrackingService;

use App\Modules\UserManagement\Models\User;
use App\Modules\ContentManagement\Models\Protocol;
use App\Modules\TrackingService\Models\TrackingLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingLogAdherenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_adherence_endpoint_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/tracking/log', []);
        $response->assertStatus(401);
    }

    public function test_log_adherence_endpoint_validates_input(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/tracking/log', [
            // Missing required fields
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['protocol_id', 'tracked_at']);
    }

    public function test_log_adherence_endpoint_logs_adherence(): void
    {
        $user = User::factory()->create();
        $protocol = Protocol::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/tracking/log', [
            'protocol_id' => $protocol->id,
            'tracked_at' => '2023-10-27',
            'notes' => 'Completed the protocol',
            'metadata' => ['duration' => 30],
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'protocol_id',
                'tracked_at',
                'notes',
                'metadata',
                'created_at',
                'updated_at',
            ],
        ]);

        $this->assertDatabaseHas('user_protocol_tracking', [
            'user_id' => $user->id,
            'protocol_id' => $protocol->id,
            'tracked_at' => '2023-10-27',
            'notes' => 'Completed the protocol',
            'metadata' => json_encode(['duration' => 30]),
        ]);
    }
}
