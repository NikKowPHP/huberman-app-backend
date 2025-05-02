<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\NotesService\Models\NoteCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class NoteCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NoteCategory $noteCategory): bool
    {
        return $user->id === $noteCategory->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, NoteCategory $noteCategory): bool
    {
        return $user->id === $noteCategory->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, NoteCategory $noteCategory): bool
    {
        return $user->id === $noteCategory->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, NoteCategory $noteCategory): bool
    {
        return $user->id === $noteCategory->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, NoteCategory $noteCategory): bool
    {
        return $user->id === $noteCategory->user_id;
    }
}
