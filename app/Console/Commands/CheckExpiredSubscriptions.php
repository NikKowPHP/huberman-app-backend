<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired subscriptions and update their status.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredSubscriptions = \App\Modules\SubscriptionBilling\Models\Subscription::where('status', 'canceled')
            ->where('ends_at', '<', now())
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->status = 'expired';
            $subscription->save();
        }

        $this->info('Checked ' . $expiredSubscriptions->count() . ' subscriptions and updated their status to expired.');
    }
}
