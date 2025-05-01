<?php

namespace App\Modules\TrackingService\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\TrackingService\Contracts\TrackingServiceInterface;
use App\Modules\TrackingService\Http\Requests\StoreTrackingLogRequest;
use App\Modules\TrackingService\Models\TrackingLog; // Import model for policy
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; // Keep Request for potential future use
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class TrackingController extends Controller
{
    protected TrackingServiceInterface $trackingService;

    public function __construct(TrackingServiceInterface $trackingService)
    {
        $this->trackingService = $trackingService;
        // Apply auth middleware to all methods in this controller
        $this->middleware('auth:sanctum');
        // Policy check for 'create' is handled by StoreTrackingLogRequest authorize method
    }

    /**
     * Store a newly created tracking log resource in storage.
     */
    public function store(StoreTrackingLogRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = Auth::user(); // Get authenticated user

        // The authorize method in StoreTrackingLogRequest already checked permission

        $log = $this->trackingService->logAdherence(
            $user,
            $validatedData['protocol_id'],
            Carbon::parse($validatedData['tracked_at']), // Ensure Carbon instance
            $validatedData['notes'] ?? null,
            $validatedData['metadata'] ?? null
        );

        // Eager load relationships if needed for the response
        $log->load(['user', 'protocol']);

        // Consider using an API Resource here for consistent responses
        return response()->json([
            'message' => 'Tracking log created successfully.',
            'data' => $log
        ], 201);
    }

    /**
     * Get adherence summary (e.g., streak) for a specific protocol.
     */
    public function getSummary(Request $request, int $protocolId): JsonResponse
    {
        $user = Auth::user();

        // Manually authorize 'viewAny' as we are viewing summary data, not specific logs
        $this->authorize('viewAny', TrackingLog::class);

        $streak = $this->trackingService->calculateStreak($user, $protocolId);

        // Add more summary data fetching here if needed (e.g., total logs, logs this month)
        $totalLogs = TrackingLog::where('user_id', $user->id)
                               ->where('protocol_id', $protocolId)
                               ->count();

        return response()->json([
            'data' => [
                'protocol_id' => $protocolId,
                'current_streak' => $streak,
                'total_logs' => $totalLogs,
                // Add other summary data here
            ]
        ]);
    }

    // Add other methods (show, update, destroy for individual logs) if needed,
    // ensuring appropriate authorization checks (e.g., using $this->authorize('update', $log))
}