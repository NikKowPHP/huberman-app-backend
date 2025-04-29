<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Modules\SubscriptionBilling\Models\Subscription;

class SubscriptionRenewalFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Subscription $subscription;

    /**
     * Create a new event instance.
     *
     * @param  Subscription  $subscription
     * @return void
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }
}
