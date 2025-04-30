<?php

namespace App\Console\Commands;

use App\Jobs\SendProtocolReminderNotification;
use App\Modules\ProtocolEngine\Models\UserReminder;
use App\Modules\UserManagement\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendDueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send out due reminders to users.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        $currentDay = $now->format('D'); // e.g., Mon, Tue

        $reminders = UserReminder::where('is_active', true)
            ->where('reminder_time', $currentTime)
            ->get();

        foreach ($reminders as $reminder) {
            // Check frequency and specific days if applicable
            if ($reminder->frequency === 'daily') {
                $this->sendReminder($reminder);
            } elseif ($reminder->frequency === 'weekly' ) {
                $this->sendReminder($reminder);
            } elseif ($reminder->frequency === 'specific_days' && in_array($currentDay, $reminder->specific_days)) {
                $this->sendReminder($reminder);
            }
        }
    }

    protected function sendReminder(UserReminder $reminder): void
    {
        SendProtocolReminderNotification::dispatch($reminder);
        // Log::info("Dispatching job for reminder {$reminder->id} to user {$reminder->user_id}");
    }
}
