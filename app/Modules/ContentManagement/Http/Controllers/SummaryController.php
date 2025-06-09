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
     * @OA\Get(
     *     path="/api/v1/summaries",
     *     summary="List all summaries",
     *     description="Returns a list of available summaries",
     *     operationId="getSummaries",
     *     tags={"Content Management"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/SummaryResource")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'Summaries index not implemented']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/summaries/{id}",
     *     summary="Get summary by ID",
     *     description="Returns a single summary with full details",
     *     operationId="getSummaryById",
     *     tags={"Content Management"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of summary to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SummaryResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Summary not found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        return response()->json(['message' => 'Summary show not implemented']);
    }
}
