<?php

namespace App\Policies;

use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface;
use App\Modules\UserManagement\Models\User;
use App\Note;
use Illuminate\Auth\Access\Response;

class NotePolicy
{
    private const MAX_FREE_NOTES = 5;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Note $note): bool
    {
        return $user->id === $note->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, SubscriptionServiceInterface $subscriptionService): bool
    {
        if ($subscriptionService->userHasActivePremiumSubscription($user)) {
            return true;
        }

        $noteCount = Note::where('user_id', $user->id)->count();
        return $noteCount < self::MAX_FREE_NOTES;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Note $note): bool
    {
        return $user->id === $note->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Note $note): bool
    {
        return $user->id === $note->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Note $note): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Note $note): bool
    {
        return false;
    }
}
