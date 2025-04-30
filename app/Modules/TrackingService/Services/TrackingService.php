<?php

namespace App\Modules\TrackingService\Services;

use App\Modules\TrackingService\Contracts\TrackingServiceInterface;
use App\Modules\UserManagement\Models\User;
use App\Modules\TrackingService\Models\TrackingLog;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class TrackingService implements TrackingServiceInterface
{
    /**
     * Log adherence for a given protocol.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  int  $protocolId
     * @param  string|null  $notes
     * @param  array|null  $metadata
     * @return \App\Modules\TrackingService\Models\TrackingLog
     */
    public function logAdherence(User $user, int $protocolId, ?string $notes = null, ?array $metadata = null): TrackingLog
    {
        return TrackingLog::create([
            'user_id' => $user->id,
            'protocol_id' => $protocolId,
            'tracked_at' => Carbon::now()->toDateString(),
            'notes' => $notes,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get the adherence streak for a user and protocol.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  int  $protocolId
     * @return int
     */
    public function getStreak(User $user, int $protocolId): int
    {
        $logs = TrackingLog::where('user_id', $user->id)
            ->where('protocol_id', $protocolId)
            ->orderBy('tracked_at', 'desc')
            ->get();

        if ($logs->isEmpty()) {
            return 0;
        }

        $streak = 1;
        $previousDate = Carbon::parse($logs->first()->tracked_at);

        foreach ($logs as $key => $log) {
            if ($key === 0) continue;

            $currentDate = Carbon::parse($log->tracked_at);
            if ($currentDate->diffInDays($previousDate) === 1) {
                $streak++;
            } else {
                break;
            }
            $previousDate = $currentDate;
        }

        return $streak;
    }

    /**
     * Get a list of tracking logs for a user.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @return \Illuminate\Support\Collection
     */
    public function getLogsForUser(User $user): Collection
    {
        return TrackingLog::where('user_id', $user->id)->get();
    }
}
