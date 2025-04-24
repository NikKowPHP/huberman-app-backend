<?php

namespace App\Modules\ContentManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContentManagement\Contracts\ContentServiceInterface;
use App\Modules\ContentManagement\Http\Resources\EpisodeResource;
use Illuminate\Http\JsonResponse;

class EpisodeController extends Controller
{
    private ContentServiceInterface $contentService;

    public function __construct(ContentServiceInterface $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $episodes = $this->contentService->getEpisodes();

        return response()->json(EpisodeResource::collection($episodes));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $episode = $this->contentService->getEpisodeDetails($id);

        return response()->json(new EpisodeResource($episode));
    }
}
