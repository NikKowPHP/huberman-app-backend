<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\NotesService\Models\NoteCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_category_can_be_created()
    {
        $noteCategory = NoteCategory::factory()->create();

        $this->assertDatabaseCount('note_categories', 1);
        $this->assertDatabaseHas('note_categories', ['id' => $noteCategory->id]);
    }

    public function test_note_category_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $noteCategory = NoteCategory::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $noteCategory->user);
        $this->assertEquals($user->id, $noteCategory->user->id);
    }

    public function test_note_category_has_many_notes()
    {
        $noteCategory = NoteCategory::factory()->create();
        // Assuming a Note model exists and has a category_id foreign key
        // \App\Modules\NotesService\Models\Note::factory()->count(2)->create(['category_id' => $noteCategory->id]);

        // $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $noteCategory->notes);
        // $this->assertCount(2, $noteCategory->notes);
        $this->markTestIncomplete('Note model relationship test needs Note model implementation.');
    }
}
