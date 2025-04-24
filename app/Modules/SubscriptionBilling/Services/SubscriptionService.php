<?php

namespace App\Modules\SubscriptionBilling\Services;

use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface;
use NotImplementedException;

class SubscriptionService implements SubscriptionServiceInterface
{
    public function userHasActivePremiumSubscription($user)
    {
        return false;
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
