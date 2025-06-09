<?php

namespace App\Modules\ContentManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContentManagement\Models\Episode;
use App\Modules\NotesService\Models\Note;
use Illuminate\Http\JsonResponse;

class EpisodeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/episodes/{id}/public-notes",
     *     summary="Get public notes for an episode",
     *     description="Returns a list of public notes associated with the specified episode",
     *     operationId="getEpisodePublicNotes",
     *     tags={"Content Management"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the episode",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Note")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Episode not found"
     *     )
     * )
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
