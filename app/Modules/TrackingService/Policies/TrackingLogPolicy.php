<?php

namespace App\Modules\TrackingService\Policies;

use App\Modules\UserManagement\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface;

class TrackingLogPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return resolve(SubscriptionServiceInterface::class)->userHasActivePremiumSubscription($user)
            ? true
            : false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  \App\Modules\TrackingService\Models\TrackingLog  $trackingLog
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, TrackingLog $trackingLog)
    {
        return resolve(SubscriptionServiceInterface::class)->userHasActivePremiumSubscription($user)
            ? true
            : false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return resolve(SubscriptionServiceInterface::class)->userHasActivePremiumSubscription($user)
            ? true
            : false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  \App\Modules\TrackingService\Models\TrackingLog  $trackingLog
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, TrackingLog $trackingLog)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  \App\Modules\TrackingService\Models\TrackingLog  $trackingLog
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, TrackingLog $trackingLog)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  \App\Modules\TrackingService\Models\TrackingLog  $trackingLog
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, TrackingLog $trackingLog)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  \App\Modules\TrackingService\Models\TrackingLog  $trackingLog
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, TrackingLog $trackingLog)
    {
        return false;
    }

    /**
     * Allow admins to do anything.
     *
     * @param  \App\Modules\UserManagement\Models\User  $user
     * @param  string  $ability
     * @return bool
     */
    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }
}
