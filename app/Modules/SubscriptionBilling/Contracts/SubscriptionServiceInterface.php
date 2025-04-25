<?php

namespace App\Modules\SubscriptionBilling\Contracts;
use App\Modules\UserManagement\Models\User; 

interface SubscriptionServiceInterface
{
    public function userHasActivePremiumSubscription($user);
    public function getUserPlan($user);
    public function handleWebhook($payload, $signature);
    public function getSubscriptionStatus($user);

     /**
     * Handles the processing of a verified Apple App Store Server Notification V2 payload.
     *
     * @param string $signedPayload The JWS payload received from Apple.
     * @return array The decoded and verified payload data.
     * @throws \Exception If verification fails or payload is invalid.
     */
    public function handleAppleNotification(string $signedPayload): array;
}
