<?php

namespace Tests\Unit;

use App\Modules\NotesService\Contracts\NoteServiceInterface;
use App\Modules\NotesService\Models\Note;
use App\Modules\NotesService\Services\NoteService;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class NoteServiceTest extends TestCase
{
    use RefreshDatabase;

    private NoteServiceInterface $noteService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->noteService = $this->app->make(NoteServiceInterface::class);
    }

    public function test_create_note()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $data = [
            'title' => 'Test Note',
            'content' => 'This is a test note.',
        ];

        $note = $this->noteService->createNote($data);

        $this->assertInstanceOf(Note::class, $note);
        $this->assertEquals($data['title'], $note->title);
        $this->assertEquals($data['content'], $note->content);
        $this->assertEquals($user->id(), $note->user_id);
        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => $data['title'],
            'content' => $data['content'],
            'user_id' => $user->id(),
        ]);
    }

    public function test_get_note()
    {
        $note = Note::factory()->create();
        $retrievedNote = $this->noteService->getNote($note->id);

        $this->assertInstanceOf(Note::class, $retrievedNote);
        $this->assertEquals($note->id, $retrievedNote->id);
    }

    public function test_update_note()
    {
        $note = Note::factory()->create();
        $data = [
            'title' => 'Updated Test Note',
            'content' => 'This is an updated test note.',
        ];

        $updatedNote = $this->noteService->updateNote($note->id, $data);

        $this->assertInstanceOf(Note::class, $updatedNote);
        $this->assertEquals($data['title'], $updatedNote->title);
        $this->assertEquals($data['content'], $updatedNote->content);
        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => $data['title'],
            'content' => $data['content'],
        ]);
    }

    public function test_delete_note()
    {
        $note = Note::factory()->create();
        $this->noteService->deleteNote($note->id);

        $this->assertDatabaseMissing('notes', [
            'id' => $note->id,
        ]);
    }

    public function test_get_user_notes_count()
    {
        $user = User::factory()->create();
        Note::factory()->count(3)->create(['user_id' => $user->id]);
        Note::factory()->count(2)->create(); // Other users' notes

        $count = $this->noteService->getUserNotesCount($user->id);

        $this->assertEquals(3, $count);
    }

    public function test_get_public_notes()
    {
        Note::factory()->count(2)->create(['is_public' => true]);
        Note::factory()->count(3)->create(['is_public' => false]);

        $publicNotes = $this->noteService->getPublicNotes();

        $this->assertCount(2, $publicNotes);
        foreach ($publicNotes as $note) {
            $this->assertTrue($note->is_public);
        }
    }
}
