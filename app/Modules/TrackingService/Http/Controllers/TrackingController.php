<?php

namespace App\Modules\TrackingService\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\TrackingService\Contracts\TrackingServiceInterface;
use App\Modules\TrackingService\Http\Requests\StoreTrackingLogRequest;
use App\Modules\TrackingService\Http\Resources\TrackingLogResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TrackingController extends Controller
{
    protected $trackingService;

    public function __construct(TrackingServiceInterface $trackingService)
    {
        $this->trackingService = $trackingService;
        $this->authorizeResource(TrackingLog::class, 'tracking_log');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Modules\TrackingService\Http\Requests\StoreTrackingLogRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTrackingLogRequest $request): JsonResponse
    {
        $trackingLog = $this->trackingService->logAdherence(
            $request->user(),
            $request->protocol_id,
            $request->notes,
            $request->metadata
        );

        return (new TrackingLogResource($trackingLog))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
