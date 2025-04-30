<?php

namespace App\Jobs;

use App\Modules\ProtocolEngine\Models\UserReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendProtocolReminderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The user reminder instance.
     *
     * @var \App\Modules\ProtocolEngine\Models\UserReminder
     */
    public UserReminder $reminder;

    /**
     * Create a new job instance.
     */
    public function __construct(UserReminder $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // TODO: Implement actual notification sending logic
        // 1. Get user associated with the reminder ($this->reminder->user)
        // 2. Get user's device token(s)
        // 3. Construct notification payload
        // 4. Send notification using appropriate service (FCM/APNS)
        // 5. Update reminder's last_sent_at timestamp

        Log::info("Job: Sending reminder notification for reminder ID: {$this->reminder->id} to user ID: {$this->reminder->user_id}");

        // Example placeholder for updating last_sent_at
        // $this->reminder->update(['last_sent_at' => now()]);
    }
}
