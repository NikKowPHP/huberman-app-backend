<?php

namespace Tests\Unit;

use App\Modules\NotesService\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_creation()
    {
        $note = Note::factory()->create();

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
        ]);
        $this->assertNotNull($note->user_id);
        $this->assertNotNull($note->content);
    }

    public function test_note_fillable()
    {
        $fillable = (new Note())->getFillable();
        $this->assertEquals(['user_id', 'title', 'content'], $fillable);
    }
}
