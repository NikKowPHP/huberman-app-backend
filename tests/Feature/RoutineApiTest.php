<?php

namespace Tests\Feature;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutineApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_get_routines()
    {
        Routine::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/routines');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'frequency',
                        'start_time',
                        'end_time',
                        'is_active'
                    ]
                ]
            ]);
    }

    public function test_create_routine()
    {
        $data = [
            'name' => 'Morning Routine',
            'description' => 'Start your day right',
            'frequency' => 'daily',
            'start_time' => '07:00',
            'end_time' => '08:00',
            'is_active' => true
        ];

        $response = $this->postJson('/api/routines', $data);

        $response->assertStatus(201)
            ->assertJson([
                'data' => $data
            ]);

        $this->assertDatabaseHas('routines', $data);
    }

    public function test_show_routine()
    {
        $routine = Routine::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/routines/{$routine->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $routine->id,
                    'name' => $routine->name,
                    'description' => $routine->description
                ]
            ]);
    }

    public function test_update_routine()
    {
        $routine = Routine::factory()->create(['user_id' => $this->user->id]);
        $updateData = [
            'name' => 'Updated Routine',
            'is_active' => false
        ];

        $response = $this->putJson("/api/routines/{$routine->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => $updateData
            ]);

        $this->assertDatabaseHas('routines', $updateData);
    }

    public function test_delete_routine()
    {
        $routine = Routine::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/routines/{$routine->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('routines', ['id' => $routine->id]);
    }

    public function test_cannot_access_other_user_routine()
    {
        $otherUser = User::factory()->create();
        $routine = Routine::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/routines/{$routine->id}");

        $response->assertStatus(403);
    }
}
