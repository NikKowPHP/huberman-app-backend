<?php

namespace Tests\Feature;

use App\Console\Commands\SendFoundationalReminders;
use App\Modules\ContentManagement\Models\Protocol;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendFoundationalRemindersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_foundational_reminders_to_free_tier_users()
    {
        Notification::fake();

        // Create a foundational protocol
        $foundationalProtocol = Protocol::factory()->create(['category' => 'foundational']);

        // Create a non-foundational protocol
        $nonFoundationalProtocol = Protocol::factory()->create(['category' => 'premium']);

        // Create a free tier user
        $freeUser = User::factory()->create();

        // Create a premium user
        $premiumUser = User::factory()->create();
        $premiumUser->subscriptions()->create(['status' => 'active']);

        // Run the command
        $this->artisan('app:send-foundational-reminders');

        // Assert that the free tier user received a notification for the foundational protocol
        Notification::assertSentTo(
            [$freeUser],
            \App\Notifications\ProtocolReminder::class,
            function ($notification, $channels, $notifiable) use ($foundationalProtocol) {
                return $notification->protocol->id === $foundationalProtocol->id;
            }
        );

        // Assert that the premium user did not receive a notification
        Notification::assertNotSentTo(
            [$premiumUser],
            \App\Notifications\ProtocolReminder::class
        );

        // Assert that no notification was sent for the non-foundational protocol
        Notification::assertNotSentTo(
            [$freeUser],
            \App\Notifications\ProtocolReminder::class,
            function ($notification, $channels, $notifiable) use ($nonFoundationalProtocol) {
                return $notification->protocol->id === $nonFoundationalProtocol->id;
            }
        );
    }
}
