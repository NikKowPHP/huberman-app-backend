<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\SubscriptionCanceled;
use App\Events\SubscriptionExpired;
use App\Events\SubscriptionRenewed;
use App\Events\SubscriptionStarted;
use Illuminate\Queue\InteractsWithQueue;

class ClearUserEntitlementCache implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  SubscriptionStarted|SubscriptionRenewed|SubscriptionCanceled|SubscriptionExpired  $event
     * @return void
     */
    public function handle($event)
    {
        if (isset($event->subscription->user_id)) {
            \Cache::tags("user:{$event->subscription->user_id}")->flush();
        } elseif (isset($event->user->id)) {
            \Cache::tags("user:{$event->user->id}")->flush();
        }
    }
}
