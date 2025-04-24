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
     * Display a listing of the resource.
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $protocol = $this->contentService->getProtocolDetails($id);

        return response()->json(new ProtocolResource($protocol));
    }
}
