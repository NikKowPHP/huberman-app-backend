<?php

namespace App\Modules\NotesService\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\NotesService\Http\Requests\CreateNoteRequest;
use App\Modules\NotesService\Http\Requests\UpdateNoteRequest;
use App\Modules\NotesService\Models\Note;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class NoteController extends Controller
{
    /**
     * Store a newly created note in storage.
     */
    public function store(CreateNoteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $note = Note::create([
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'content' => $data['content'],
        ]);

        return response()->json([
            'data' => $note,
        ], 201);
    }

    /**
     * Display a listing of the user's notes.
     */
    public function index(): JsonResponse
    {
        $notes = Note::where('user_id', auth()->id())->get();

        return response()->json([
            'data' => $notes,
        ]);
    }

    /**
     * Display the specified note.
     */
    public function show(Note $note): JsonResponse
    {
        if ($note->user_id !== auth()->id()) {
            return response()->json(['message' => 'Not Found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $note,
        ]);
    }

    /**
     * Update the specified note in storage.
     */
    public function update(UpdateNoteRequest $request, Note $note): JsonResponse
    {
        if ($note->user_id !== auth()->id()) {
            return response()->json(['message' => 'Not Found'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->validated();

        $note->update($data);

        return response()->json([
            'data' => $note,
        ]);
    }

    /**
     * Remove the specified note from storage.
     */
    public function destroy(Note $note): JsonResponse
    {
        if ($note->user_id !== auth()->id()) {
            return response()->json(['message' => 'Not Found'], Response::HTTP_NOT_FOUND);
        }

        $note->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Display a listing of public notes for a given episode.
     */
    public function listPublicNotesForEpisode(Episode $episode): JsonResponse
    {
        $notes = Note::where('episode_id', $episode->id)
            ->where('is_public', true)
            ->get();

        return response()->json([
            'data' => $notes,
        ]);
    }
}
