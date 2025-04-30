<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\SendProtocolReminderNotification;
use App\Models\User;
use App\Models\UserReminder;
use App\Notifications\ProtocolReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class SendProtocolReminderNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the job retrieves data, fetches token, constructs payload, and mocks Notification::send().
     */
    public function test_job_sends_protocol_reminder_notification()
    {
        Notification::fake();

        // Create a user and a reminder
        $user = User::factory()->create(['device_token' => 'fake_device_token']);
        $reminder = UserReminder::factory()->create(['user_id' => $user->id]);

        // Dispatch the job
        SendProtocolReminderNotification::dispatch($reminder);

        // Assert that the notification was sent to the user
        Notification::assertSentTo(
            $user,
            ProtocolReminder::class,
            function ($notification, $channels) use ($reminder) {
                // Assert that the notification is sent via fcm and apns channels
                $this->assertContains('fcm', $channels);
                $this->assertContains('apns', $channels);

                // You would add assertions here to check the notification payload
                // based on how you implement the toFcm and toApns methods
                // For now, we just check the basic structure.
                $fcmPayload = $notification->toFcm($reminder->user);
                $apnsPayload = $notification->toApns($reminder->user);

                $this->assertIsArray($fcmPayload);
                $this->assertIsArray($apnsPayload);

                return true;
            }
        );
    }
}
