<?php

namespace App\Modules\TrackingService\Policies;

use App\Modules\UserManagement\Models\User;
use App\Modules\TrackingService\Models\TrackingLog;
use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface; // Assuming this is how premium status is checked
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TrackingLogPolicy
{
    use HandlesAuthorization;

    protected SubscriptionServiceInterface $subscriptionService;

    public function __construct(SubscriptionServiceInterface $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $this->subscriptionService->userHasActivePremiumSubscription($user)
                    ? Response::allow()
                    : Response::deny('Premium subscription required to view tracking logs.');
    }

    /**
     * Determine whether the user can view the model.
     * Note: Typically you'd also check ownership here if relevant ($user->id === $trackingLog->user_id)
     * But the primary gate is premium status according to the checklist.
     */
    public function view(User $user, TrackingLog $trackingLog): Response
    {
        return $this->subscriptionService->userHasActivePremiumSubscription($user)
                    ? Response::allow()
                    : Response::deny('Premium subscription required to view this tracking log.');
        // Add ownership check if needed:
        // && $user->id === $trackingLog->user_id
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $this->subscriptionService->userHasActivePremiumSubscription($user)
                    ? Response::allow()
                    : Response::deny('Premium subscription required to create tracking logs.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TrackingLog $trackingLog): Response
    {
        // Check premium status AND ownership
        return $this->subscriptionService->userHasActivePremiumSubscription($user) && $user->id === $trackingLog->user_id
                    ? Response::allow()
                    : Response::deny('You do not have permission to update this tracking log.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TrackingLog $trackingLog): Response
    {
         // Check premium status AND ownership
        return $this->subscriptionService->userHasActivePremiumSubscription($user) && $user->id === $trackingLog->user_id
                    ? Response::allow()
                    : Response::deny('You do not have permission to delete this tracking log.');
    }

    /**
     * Determine whether the user can restore the model.
     * (Only relevant if using Soft Deletes on TrackingLog)
     */
    // public function restore(User $user, TrackingLog $trackingLog): Response
    // {
    //     return $this->subscriptionService->userHasActivePremiumSubscription($user) && $user->id === $trackingLog->user_id
    //                 ? Response::allow()
    //                 : Response::deny('You do not have permission to restore this tracking log.');
    // }

    /**
     * Determine whether the user can permanently delete the model.
     * (Only relevant if using Soft Deletes on TrackingLog)
     */
    // public function forceDelete(User $user, TrackingLog $trackingLog): Response
    // {
    //     return $this->subscriptionService->userHasActivePremiumSubscription($user) && $user->id === $trackingLog->user_id
    //                 ? Response::allow()
    //                 : Response::deny('You do not have permission to force delete this tracking log.');
    // }
}