<?php

namespace App\Modules\TrackingService\Services;

use App\Modules\TrackingService\Contracts\TrackingServiceInterface;
use App\Modules\TrackingService\Models\TrackingLog;
use App\Modules\UserManagement\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon; // Use Illuminate\Support\Carbon
use Illuminate\Support\Facades\DB;

class TrackingService implements TrackingServiceInterface
{
    /**
     * Log adherence for a specific protocol on a given date.
     * Implements the specific logAdherence method from the interface.
     */
    public function logAdherence(User $user, int $protocolId, Carbon $date, ?string $notes = null, ?array $metadata = null): TrackingLog
    {
        // Use updateOrCreate to prevent duplicates for the same user, protocol, and day.
        $log = TrackingLog::updateOrCreate(
            [
                'user_id' => $user->id,
                'protocol_id' => $protocolId,
                'tracked_at' => $date->toDateString(), // Ensure only the date part is used for matching
            ],
            [
                'notes' => $notes,
                'metadata' => $metadata,
                // tracked_at is intentionally part of the matching condition, not the update values
            ]
        );

        return $log;
    }

    /**
     * Get tracking logs for a user, optionally filtered by protocol and date range.
     * Implements the specific getUserTrackingData method from the interface.
     */
    public function getUserTrackingData(User $user, ?int $protocolId = null, ?array $dateRange = null): Collection
    {
        $query = TrackingLog::where('user_id', $user->id)
                        ->orderBy('tracked_at', 'desc'); // Order by most recent first

        if ($protocolId !== null) {
            $query->where('protocol_id', $protocolId);
        }

        if ($dateRange !== null && isset($dateRange['start']) && isset($dateRange['end'])) {
            // Ensure dates are Carbon instances or valid date strings
            $startDate = Carbon::parse($dateRange['start'])->startOfDay();
            $endDate = Carbon::parse($dateRange['end'])->endOfDay();
            $query->whereBetween('tracked_at', [$startDate, $endDate]);
        }

        return $query->get();
    }

    /**
     * Calculate the current adherence streak for a given protocol for the user.
     * Implements the specific calculateStreak method from the interface.
     */
    public function calculateStreak(User $user, int $protocolId): int
    {
        $logs = TrackingLog::where('user_id', $user->id)
            ->where('protocol_id', $protocolId)
            ->orderBy('tracked_at', 'desc')
            ->pluck('tracked_at') // Get only the dates
            ->map(fn ($date) => Carbon::parse($date)->startOfDay()); // Ensure Carbon objects at start of day

        if ($logs->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $latestLogDate = $logs->first();
        if (!$latestLogDate->isSameDay($today) && !$latestLogDate->isSameDay($yesterday)) {
            return 0; // Streak broken if latest log isn't today or yesterday
        }

        $expectedDate = $today;
        if ($latestLogDate->isSameDay($yesterday)) {
             $expectedDate = $yesterday; // Start checking from yesterday if today wasn't logged
        }

        foreach ($logs as $logDate) {
            if ($logDate->isSameDay($expectedDate)) {
                $streak++;
                $expectedDate->subDay(); // Look for the previous day
            } elseif ($logDate->lt($expectedDate)) {
                 break; // Gap detected
            }
            // Ignore dates > expectedDate (duplicates or sorting issues)
        }

        return $streak;
    }

     /**
      * Get a specific tracking log entry by ID (ensure ownership).
      * Implements the specific getTrackingLogById method from the interface.
      */
     public function getTrackingLogById(User $user, int $logId): ?TrackingLog
     {
         return TrackingLog::where('user_id', $user->id)->find($logId);
     }

     /**
      * Update a specific tracking log entry (ensure ownership).
      * Implements the specific updateTrackingLog method from the interface.
      */
     public function updateTrackingLog(User $user, int $logId, array $data): ?TrackingLog
     {
         $log = $this->getTrackingLogById($user, $logId); // Use the correctly named method
         if ($log) {
             // Filter data to only include fillable attributes
             $fillableData = array_intersect_key($data, array_flip((new TrackingLog)->getFillable()));

             // Prevent updating user_id or protocol_id usually
             unset($fillableData['user_id'], $fillableData['protocol_id']);

             // Ensure tracked_at is handled correctly if updatable
             if (isset($fillableData['tracked_at'])) {
                  $fillableData['tracked_at'] = Carbon::parse($fillableData['tracked_at'])->toDateString();
             }

             $log->update($fillableData);
             return $log->refresh(); // Refresh to get updated data
         }
         return null;
     }

     /**
      * Delete a specific tracking log entry (ensure ownership).
      * Implements the specific deleteTrackingLog method from the interface.
      */
     public function deleteTrackingLog(User $user, int $logId): bool
     {
         $log = $this->getTrackingLogById($user, $logId); // Use the correctly named method
         if ($log) {
             return $log->delete();
         }
         return false;
     }

    // --- Methods NOT explicitly in the error message, but likely needed based on previous interface ---
    // If these methods ARE NOT in your current TrackingServiceInterface, REMOVE THEM.
    // If they ARE in the interface, keep them.

    /**
     * Create a new tracking log entry (Generic version, might be redundant if logAdherence covers all cases).
     * Keep ONLY if TrackingServiceInterface defines this exact signature separately from logAdherence.
     */
    // public function create(User $user, array $data): TrackingLog
    // {
    //     // Implementation would depend on what 'data' is expected here
    //     // Likely similar to logAdherence but maybe without specific protocol/date assumptions
    //     return TrackingLog::create(['user_id' => $user->id] + $data); // Example
    // }

    /**
     * Get all tracking logs for a user. (Keep ONLY if interface defines this signature)
     */
    // public function getAll(User $user): Collection
    // {
    //     return TrackingLog::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
    // }

    /**
     * Get a specific tracking log entry by ID (ensure ownership). (Keep ONLY if interface defines this signature)
     */
    // public function getById(User $user, int $id): ?TrackingLog
    // {
    //     return TrackingLog::where('user_id', $user->id)->find($id);
    // }

    /**
     * Update a specific tracking log entry. (Keep ONLY if interface defines this signature)
     */
    // public function update(User $user, int $id, array $data): ?TrackingLog
    // {
    //     $log = $this->getById($user, $id);
    //     if ($log) {
    //         $log->update($data);
    //         return $log;
    //     }
    //     return null;
    // }

    /**
     * Delete a specific tracking log entry. (Keep ONLY if interface defines this signature)
     */
    // public function delete(User $user, int $id): bool
    // {
    //     $log = $this->getById($user, $id);
    //     if ($log) {
    //         return $log->delete();
    //     }
    //     return false;
    // }

    /**
     * Calculate the current adherence streak for the user (generic version). (Keep ONLY if interface defines this signature)
     */
    // public function getStreak(User $user): int
    // {
    //     // Implementation would need clarification - streak for what? Overall app usage?
    //     return 0; // Placeholder
    // }

    /**
     * Get public tracking logs. (Keep ONLY if interface defines this signature)
     */
    // public function getPublicList(User $user): Collection
    // {
    //     // Placeholder: Define what constitutes a "public" log.
    //     return new Collection();
    // }
}