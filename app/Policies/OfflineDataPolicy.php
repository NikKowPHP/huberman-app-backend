<?php

namespace App\Policies;

use App\Models\User;
use App\OfflineData;
use Illuminate\Auth\Access\HandlesAuthorization;

class OfflineDataPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any offline data.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the offline data.
     *
     * @param  \App\Models\User  $user
     * @param  \App\OfflineData  $offlineData
     * @return mixed
     */
    public function view(User $user, OfflineData $offlineData)
    {
        return $user->id === $offlineData->user_id;
    }

    /**
     * Determine whether the user can create offline data.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the offline data.
     *
     * @param  \App\Models\User  $user
     * @param  \App\OfflineData  $offlineData
     * @return mixed
     */
    public function update(User $user, OfflineData $offlineData)
    {
        return $user->id === $offlineData->user_id;
    }

    /**
     * Determine whether the user can delete the offline data.
     *
     * @param  \App\Models\User  $user
     * @param  \App\OfflineData  $offlineData
     * @return mixed
     */
    public function delete(User $user, OfflineData $offlineData)
    {
        return $user->id === $offlineData->user_id;
    }

    /**
     * Determine whether the user can restore the offline data.
     *
     * @param  \App\Models\User  $user
     * @param  \App\OfflineData  $offlineData
     * @return mixed
     */
    public function restore(User $user, OfflineData $offlineData)
    {
        return $user->id === $offlineData->user_id;
    }

    /**
     * Determine whether the user can permanently delete the offline data.
     *
     * @param  \App\Models\User  $user
     * @param  \App\OfflineData  $offlineData
     * @return mixed
     */
    public function forceDelete(User $user, OfflineData $offlineData)
    {
        return $user->id === $offlineData->user_id;
    }
}
