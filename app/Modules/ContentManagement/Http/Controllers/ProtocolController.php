<?php

namespace App\Modules\ContentManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContentManagement\Contracts\ContentServiceInterface;
use App\Modules\ContentManagement\Http\Resources\ProtocolResource;
use Illuminate\Http\JsonResponse;

class ProtocolController extends Controller
{
    private ContentServiceInterface $contentService;

    public function __construct(ContentServiceInterface $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/protocols",
     *     summary="List all protocols",
     *     description="Returns a list of protocols. Premium users get all protocols, free users only get free ones.",
     *     operationId="getProtocols",
     *     tags={"Content Management"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ProtocolResource")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        if ($user && app(SubscriptionServiceInterface::class)->userHasActivePremiumSubscription($user)) {
            $protocols = $this->contentService->getProtocols();
        } else {
            $protocols = $this->contentService->getProtocols()->where('is_free', true);
        }

        return response()->json(ProtocolResource::collection($protocols));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/protocols/{id}",
     *     summary="Get protocol by ID",
     *     description="Returns a single protocol with full details",
     *     operationId="getProtocolById",
     *     tags={"Content Management"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of protocol to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ProtocolResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Protocol not found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $protocol = $this->contentService->getProtocolDetails($id);

        return response()->json(new ProtocolResource($protocol));
    }
}
