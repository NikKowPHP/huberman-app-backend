<?php

namespace App\Policies;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoutinePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Routine $routine)
    {
        return $user->id === $routine->user_id;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Routine $routine)
    {
        return $user->id === $routine->user_id;
    }

    public function delete(User $user, Routine $routine)
    {
        return $user->id === $routine->user_id;
    }
}
