<?php

namespace App\Modules\TrackingService\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\TrackingService\Contracts\TrackingServiceInterface;
use App\Modules\TrackingService\Http\Requests\StoreTrackingLogRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    /**
     * @var TrackingServiceInterface
     */
    protected $trackingService;

    public function __construct(TrackingServiceInterface $trackingService)
    {
        $this->trackingService = $trackingService;
        $this->middleware('auth:sanctum');
        $this->middleware('can:create,App\Modules\TrackingService\Models\TrackingLog');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Modules\TrackingService\Http\Requests\StoreTrackingLogRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTrackingLogRequest $request): JsonResponse
    {
        $user = Auth::user();
        $trackingLog = $this->trackingService->create($user, $request->validated());

        return response()->json($trackingLog, 201);
    }
}
