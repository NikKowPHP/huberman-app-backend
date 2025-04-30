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

    public function test_list_user_notes()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $notes = Note::factory(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/notes');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'content',
                    'user_id',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);

        $response->assertJsonCount(3, 'data');

        foreach ($notes as $note) {
            $response->assertJsonFragment([
                'id' => $note->id,
                'title' => $note->title,
                'content' => $note->content,
                'user_id' => $user->id,
            ]);
        }
    }

    public function test_show_note()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $note = Note::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/notes/{$note->id}");

        $response->assertStatus(200);
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

        $response->assertJson([
            'data' => [
                'id' => $note->id,
                'title' => $note->title,
                'content' => $note->content,
                'user_id' => $user->id,
            ]
        ]);
    }

    public function test_show_note_not_found()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/notes/999");

        $response->assertStatus(404);
    }

    public function test_update_note()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $note = Note::factory()->create(['user_id' => $user->id]);

        $data = [
            'title' => 'Updated Test Note',
            'content' => 'This is an updated test note.',
        ];

        $response = $this->putJson("/api/v1/notes/{$note->id}", $data);

        $response->assertStatus(200);
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

        $response->assertJson([
            'data' => [
                'id' => $note->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'user_id' => $user->id,
            ]
        ]);

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => $data['title'],
            'content' => $data['content'],
            'user_id' => $user->id,
        ]);
    }

    public function test_update_note_not_found()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'title' => 'Updated Test Note',
            'content' => 'This is an updated test note.',
        ];

        $response = $this->putJson("/api/v1/notes/999", $data);

        $response->assertStatus(404);
    }

    public function test_delete_note()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $note = Note::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/notes/{$note->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('notes', [
            'id' => $note->id,
        ]);
    }

    public function test_delete_note_not_found()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/notes/999");

        $response->assertStatus(404);
    }

    public function test_list_public_notes_for_episode()
    {
        $episode = Episode::factory()->create();
        $publicNote1 = Note::factory()->create(['episode_id' => $episode->id, 'is_public' => true]);
        $publicNote2 = Note::factory()->create(['episode_id' => $episode->id, 'is_public' => true]);
        $privateNote = Note::factory()->create(['episode_id' => $episode->id, 'is_public' => false]);

        $response = $this->getJson("/api/v1/episodes/{$episode->id}/public-notes");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'content',
                    'user_id',
                    'episode_id',
                    'is_public',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);

        $response->assertJsonCount(2, 'data');

        $response->assertJsonFragment([
            'id' => $publicNote1->id,
            'title' => $publicNote1->title,
            'content' => $publicNote1->content,
            'episode_id' => $episode->id,
            'is_public' => true,
        ]);

        $response->assertJsonFragment([
            'id' => $publicNote2->id,
            'title' => $publicNote2->title,
            'content' => $publicNote2->content,
            'episode_id' => $episode->id,
            'is_public' => true,
        ]);

        $response->assertJsonMissing([
            'id' => $privateNote->id,
        ]);
    }
}
