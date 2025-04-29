<?php

namespace App\Listeners;

use App\Events\SubscriptionExpired;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Modules\SubscriptionBilling\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionExpiredListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  \App\Events\SubscriptionExpired  $event
     * @return void
     */
    public function handle(SubscriptionExpired $event)
    {
        $subscription = $event->subscription;

        try {
            // Update subscription status to expired
            $subscription->status = 'expired';
            $subscription->save();

            // Log the event
            Log::info("Subscription expired for subscription ID: {$subscription->id}");

            // Dispatch any other necessary events
            // event(new SubscriptionExpiredEvent($subscription)); // Example: If you have a SubscriptionExpiredEvent event
        } catch (\Exception $e) {
            Log::error("Failed to handle SubscriptionExpired event for subscription ID: {$subscription->id}. Error: {$e->getMessage()}");
            // Optionally, you might want to retry the job or take other actions
            $this->release(60); // Retry after 60 seconds
        }
    }
}
