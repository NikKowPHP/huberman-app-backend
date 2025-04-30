<?php

namespace App\Modules\ContentManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContentManagement\Models\Episode;
use App\Modules\NotesService\Models\Note;
use Illuminate\Http\JsonResponse;

class EpisodeController extends Controller
{
    /**
     * Display a listing of the public notes for the specified episode.
     */
    public function publicNotes(Episode $episode): JsonResponse
    {
        $notes = Note::where('episode_id', $episode->id)
            ->where('is_public', true)
            ->get();

        return response()->json([
            'data' => $notes,
        ]);
    }
}
