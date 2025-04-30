<?php

namespace App\Modules\ProtocolEngine\Policies;

use App\Modules\ProtocolEngine\Models\UserReminder;
use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface;
use App\Modules\UserManagement\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReminderPolicy
{
    use HandlesAuthorization;

    protected SubscriptionServiceInterface $subscriptionService;

    /**
     * Create a new policy instance.
     * Inject the Subscription Service to check premium status.
     */
    public function __construct(SubscriptionServiceInterface $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Determine whether the user can view any models.
     * Any authenticated user can potentially list their own reminders.
     * The actual filtering happens in the controller/service.
     */
    public function viewAny(User $user): bool
    {
        return true; // Any authenticated user can attempt to view the list
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserReminder $reminder): bool
    {
        return $user->id === $reminder->user_id;
    }

    /**
     * Determine whether the user can create models.
     * Only premium users can create custom reminders.
     */
    public function create(User $user): bool
    {
        return $this->subscriptionService->isUserPremium($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserReminder $reminder): bool
    {
        return $user->id === $reminder->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserReminder $reminder): bool
    {
        return $user->id === $reminder->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     * (Not applicable as model doesn't use SoftDeletes)
     */
    // public function restore(User $user, UserReminder $reminder): bool
    // {
    //     return false;
    // }

    /**
     * Determine whether the user can permanently delete the model.
     * (Not applicable as model doesn't use SoftDeletes)
     */
    // public function forceDelete(User $user, UserReminder $reminder): bool
    // {
    //     return false;
    // }
}
