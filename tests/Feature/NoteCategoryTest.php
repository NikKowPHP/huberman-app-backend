<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\NoteCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_attach_category_to_note()
    {
        $note = Note::factory()->create(['user_id' => $this->user->id]);
        $category = NoteCategory::factory()->create();

        $response = $this->postJson("/api/notes/{$note->id}/categories", [
            'category_id' => $category->id
        ]);

        $response->assertStatus(201)
            ->assertJson(['attached' => [$category->id]]);

        $this->assertTrue($note->categories->contains($category));
    }
}
