<?php

namespace App\Modules\TrackingService\Policies;

use App\Modules\UserManagement\Models\User;
use App\Modules\TrackingService\Models\TrackingLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class TrackingLogPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasActivePremiumSubscription();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  \App\Modules\TrackingService\Models\TrackingLog  $trackingLog
     * @return mixed
     */
    public function view(User $user, TrackingLog $trackingLog)
    {
        return $user->hasActivePremiumSubscription();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasActivePremiumSubscription();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  \App\Modules\TrackingService\Models\TrackingLog  $trackingLog
     * @return mixed
     */
    public function update(User $user, TrackingLog $trackingLog)
    {
        return $user->hasActivePremiumSubscription();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  \App\Modules\TrackingService\Models\TrackingLog  $trackingLog
     * @return mixed
     */
    public function delete(User $user, TrackingLog $trackingLog)
    {
        return $user->hasActivePremiumSubscription();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  \App\Modules\TrackingService\Models\TrackingLog  $trackingLog
     * @return mixed
     */
    public function restore(User $user, TrackingLog $trackingLog)
    {
        return $user->hasActivePremiumSubscription();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  \App\Modules\TrackingService\Models\TrackingLog  $trackingLog
     * @return mixed
     */
    public function forceDelete(User $user, TrackingLog $trackingLog)
    {
        return $user->hasActivePremiumSubscription();
    }
}
