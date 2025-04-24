<?php

namespace App\Modules\SubscriptionBilling\Contracts;

interface SubscriptionServiceInterface
{
    public function userHasActivePremiumSubscription($user);
    public function getUserPlan($user);
    public function handleWebhook($payload, $signature);
    public function getSubscriptionStatus($user);
}
