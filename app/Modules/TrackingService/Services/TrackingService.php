<?php

namespace App\Modules\TrackingService\Services;

use App\Modules\TrackingService\Contracts\TrackingServiceInterface;
use App\Modules\TrackingService\Models\TrackingLog;
use App\Modules\UserManagement\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class TrackingService implements TrackingServiceInterface
{
    /**
     * Create a new tracking log.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  array  $data
     * @return \App\Modules\TrackingService\Models\TrackingLog
     */
    public function create(User $user, array $data): TrackingLog
    {
        return TrackingLog::create([
            'user_id' => $user->id,
            'data' => $data,
        ]);
    }

    /**
     * Get a tracking log by ID.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  int  $id
     * @return \App\Modules\TrackingService\Models\TrackingLog|null
     */
    public function getById(User $user, int $id): ?TrackingLog
    {
        return TrackingLog::where('user_id', $user->id)->find($id);
    }

    /**
     * Get all tracking logs for a user.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(User $user): Collection
    {
        return TrackingLog::where('user_id', $user->id)->get();
    }

    /**
     * Update a tracking log.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  int  $id
     * @param  array  $data
     * @return \App\Modules\TrackingService\Models\TrackingLog|null
     */
    public function update(User $user, int $id, array $data): ?TrackingLog
    {
        $trackingLog = $this->getById($user, $id);

        if (!$trackingLog) {
            return null;
        }

        $trackingLog->update($data);

        return $trackingLog;
    }

    /**
     * Delete a tracking log.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  int  $id
     * @return bool
     */
    public function delete(User $user, int $id): bool
    {
        $trackingLog = $this->getById($user, $id);

        if (!$trackingLog) {
            return false;
        }

        return $trackingLog->delete();
    }

    /**
     * Get the streak for a user.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @return int
     */
    public function getStreak(User $user): int
    {
        $today = Carbon::today();
        $logs = $this->getAll($user)
            ->sortByDesc('created_at')
            ->values();

        $streak = 0;
        foreach ($logs as $log) {
            $logDate = Carbon::parse($log->created_at)->toDateString();
            $diff = $today->diffInDays($logDate);

            if ($diff === 0) {
                $streak++;
                $today = $today->subDay();
            } else if ($diff === 1) {
                $streak++;
                $today = Carbon::parse($log->created_at)->subDay()->toDateString();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Get the public list of tracking logs.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPublicList(User $user): Collection
    {
        // Assuming there's a way to determine if a log is public.
        // For example, a 'is_public' column in the tracking_logs table.
        return TrackingLog::where('is_public', true)->get();
    }
}
