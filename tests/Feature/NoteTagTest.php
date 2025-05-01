<?php

namespace Tests\Feature;

use App\Models\NoteTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTagTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this.user);
    }

    public function test_can_create_tag()
    {
        $response = $this->postJson('/api/tags', [
            'name' => 'Test Tag',
            'color' => '#ff0000'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Test Tag',
                'color' => '#ff0000'
            ]);
    }

    public function test_can_list_tags()
    {
        NoteTag::factory()->count(3)->create();

        $response = $this->getJson('/api/tags');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_update_tag()
    {
        $tag = NoteTag::factory()->create();

        $response = $this->putJson("/api/tags/{$tag->id}", [
            'name' => 'Updated Tag',
            'color' => '#00ff00'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Updated Tag',
                'color' => '#00ff00'
            ]);
    }

    public function test_can_delete_tag()
    {
        $tag = NoteTag::factory()->create();

        $response = $this->deleteJson("/api/tags/{$tag->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('note_tags', ['id' => $tag->id]);
    }
}
