<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Modules\SubscriptionBilling\Models\Subscription;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CheckExpiredSubscriptionsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test that the command finds past canceled subscriptions and sets status to expired.
     */
    public function test_command_finds_past_canceled_subs_and_sets_status_to_expired(): void
    {
       // Create a subscription
        $subscription = Subscription::factory()->connection('sqlite')->create([
            'status' => 'canceled',
            'ends_at' => Carbon::yesterday(),
        ]);

        // Run the command
        Artisan::call('subscriptions:check-expired');

        // Refresh the subscription from the database
        $subscription->refresh();

        // Assert that the subscription's status is now expired
        $this->assertEquals('expired', $subscription->status);
    }
}
