<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class OfflineDataApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetchData_returns_users_offline_data()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/offline-data')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_syncData_creates_or_updates_users_offline_data()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'data' => [
                ['key' => 'test_key_1', 'value' => 'test_value_1'],
                ['key' => 'test_key_2', 'value' => 'test_value_2'],
            ]
        ];

        $this->postJson('/api/offline-data/sync', $data)
            ->assertStatus(200)
            ->assertJson(['message' => 'Data synced successfully']);

        $this->assertDatabaseHas('offline_data', ['user_id' => $user->id, 'key' => 'test_key_1', 'value' => 'test_value_1']);
    }
}
