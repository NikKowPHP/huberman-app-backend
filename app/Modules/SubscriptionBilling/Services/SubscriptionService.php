<?php

namespace App\Modules\SubscriptionBilling\Services;

use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface;
use App\Modules\SubscriptionBilling\Models\Subscription; // Added import
use Illuminate\Support\Facades\Cache;
use NotImplementedException;

class SubscriptionService implements SubscriptionServiceInterface
{
    public function userHasActivePremiumSubscription($user): bool
    {
        return Cache::tags(['user:' . $user->id])->remember(
            'premium_subscription',
            now()->addMinutes(60),
            function () use ($user) {
                // Check for active or trialing status and premium plan name
                $subscription = Subscription::query()
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['active', 'trialing']) // Check for active or trialing
                    // ->where('ends_at', '>', now()) // Removed ends_at check, status is sufficient
                    ->whereHas('plan', function ($query) {
                        $query->where('name', 'Premium'); // Check plan name instead of type
                    })
                    ->exists(); // Use exists() for boolean check

                return $subscription; // exists() returns boolean directly
            }
        );
    }

    public function getUserPlan($user)
    {
        return null;
    }

    public function handleWebhook($payload, $signature)
    {
        throw new NotImplementedException();
    }

    public function getSubscriptionStatus($user)
    {
        return null;
    }
}
