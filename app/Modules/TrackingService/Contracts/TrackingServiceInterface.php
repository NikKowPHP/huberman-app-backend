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
}
