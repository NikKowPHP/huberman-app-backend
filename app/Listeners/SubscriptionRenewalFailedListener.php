<?php

namespace App\Listeners;

use App\Events\SubscriptionRenewalFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Modules\SubscriptionBilling\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionRenewalFailedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  \App\Events\SubscriptionRenewalFailed  $event
     * @return void
     */
    public function handle(SubscriptionRenewalFailed $event)
    {
        $subscription = $event->subscription;

        try {
            // Update subscription status to past_due
            $subscription->status = 'past_due';
            $subscription->save();

            // Log the event
            Log::info("Subscription renewal failed for subscription ID: {$subscription->id}");

            // Dispatch any other necessary events
            // event(new SubscriptionPastDue($subscription)); // Example: If you have a SubscriptionPastDue event
        } catch (\Exception $e) {
            Log::error("Failed to handle SubscriptionRenewalFailed event for subscription ID: {$subscription->id}. Error: {$e->getMessage()}");
            // Optionally, you might want to retry the job or take other actions
            $this->release(60); // Retry after 60 seconds
        }
    }
}
