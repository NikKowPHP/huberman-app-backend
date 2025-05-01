<?php

namespace App\Modules\TrackingService\Contracts;

use App\Modules\TrackingService\Models\TrackingLog;
use App\Modules\UserManagement\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface TrackingServiceInterface
{
    /**
     * Create a new tracking log.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  array  $data
     * @return \App\Modules\TrackingService\Models\TrackingLog
     */
    public function create(User $user, array $data): TrackingLog;

    /**
     * Get a tracking log by ID.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  int  $id
     * @return \App\Modules\TrackingService\Models\TrackingLog|null
     */
    public function getById(User $user, int $id): ?TrackingLog;

    /**
     * Get all tracking logs for a user.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(User $user): Collection;

    /**
     * Update a tracking log.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  int  $id
     * @param  array  $data
     * @return \App\Modules\TrackingService\Models\TrackingLog|null
     */
    public function update(User $user, int $id, array $data): ?TrackingLog;

    /**
     * Delete a tracking log.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  int  $id
     * @return bool
     */
    public function delete(User $user, int $id): bool;

    /**
     * Get the streak for a user.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @return int
     */
    public function getStreak(User $user): int;

    /**
     * Get the public list of tracking logs.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPublicList(User $user): Collection;


     /**
     * Log adherence for a specific protocol on a given date.
     */
    public function logAdherence(User $user, int $protocolId, Carbon $date, ?string $notes = null, ?array $metadata = null): TrackingLog;

    /**
     * Get tracking logs for a user, optionally filtered by protocol and date range.
     */
    public function getUserTrackingData(User $user, ?int $protocolId = null, ?array $dateRange = null): Collection;

    /**
     * Calculate the current adherence streak for a given protocol for the user.
     * Definition: Number of consecutive days ending today (or yesterday) the protocol was logged.
     */
    public function calculateStreak(User $user, int $protocolId): int;

    /**
     * Get a specific tracking log entry by ID (ensure ownership).
     */
    public function getTrackingLogById(User $user, int $logId): ?TrackingLog;

     /**
      * Update a specific tracking log entry (ensure ownership).
      */
    public function updateTrackingLog(User $user, int $logId, array $data): ?TrackingLog;

     /**
      * Delete a specific tracking log entry (ensure ownership).
      */
    public function deleteTrackingLog(User $user, int $logId): bool;
}
