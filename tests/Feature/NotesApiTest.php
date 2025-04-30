<?php

namespace Tests\Feature;

use App\Modules\NotesService\Models\Note;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class NotesApiTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_create_note()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'title' => 'Test Note',
            'content' => 'This is a test note.',
        ];

        $response = $this->postJson('/api/v1/notes', $data);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'content',
                'user_id',
                'created_at',
                'updated_at',
            ]
        ]);

        $this->assertDatabaseHas('notes', [
            'title' => $data['title'],
            'content' => $data['content'],
            'user_id' => $user->id,
        ]);
    }
}
