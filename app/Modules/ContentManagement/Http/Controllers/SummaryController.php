<?php

namespace App\Modules\ContentManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContentManagement\Contracts\ContentServiceInterface;
use App\Modules\ContentManagement\Http\Resources\SummaryResource;
use Illuminate\Http\JsonResponse;

class SummaryController extends Controller
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
        return response()->json(['message' => 'Summaries index not implemented']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return response()->json(['message' => 'Summary show not implemented']);
    }
}
