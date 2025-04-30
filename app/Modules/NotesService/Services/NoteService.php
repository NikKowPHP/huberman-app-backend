<?php

namespace App\Modules\NotesService\Services;

use App\Modules\NotesService\Contracts\NoteServiceInterface;
use App\Modules\NotesService\Models\Note;
use Illuminate\Support\Facades\Auth;

class NoteService implements NoteServiceInterface
{
    public function createNote(array $data): Note
    {
        $data['user_id'] = Auth::id();
        $data['episode_id'] = $data['episode_id'] ?? null; // Allow null if no episode_id is provided
        return Note::create($data);
    }

    public function getNote(int $id): ?Note
    {
        return Note::findOrFail($id);
    }

    public function updateNote(int $id, array $data): Note
    {
        $note = Note::findOrFail($id);
        $note->update($data);
        return $note;
    }

    public function deleteNote(int $id): void
    {
        $note = Note::findOrFail($id);
        $note->delete();
    }

    public function getUserNotesCount(int $userId): int
    {
        return Note::where('user_id', $userId)->count();
    }

    public function getPublicNotes()
    {
        return Note::where('is_public', true)->get();
    }
}
