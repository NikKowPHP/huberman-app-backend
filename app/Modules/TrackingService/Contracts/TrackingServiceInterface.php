<?php

namespace App\Modules\TrackingService\Contracts;

use App\Modules\UserManagement\Models\User;
use App\Modules\TrackingService\Models\TrackingLog;
use Illuminate\Support\Collection;

interface TrackingServiceInterface
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
    public function logAdherence(User $user, int $protocolId, ?string $notes = null, ?array $metadata = null): TrackingLog;

    /**
     * Get the adherence streak for a user and protocol.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  int  $protocolId
     * @return int
     */
    public function getStreak(User $user, int $protocolId): int;

    /**
     * Get a list of tracking logs for a user.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @return \Illuminate\Support\Collection
     */
    public function getLogsForUser(User $user): Collection;
}
