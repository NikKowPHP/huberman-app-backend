<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendFoundationalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-foundational-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $protocols = Protocol::foundational()->get();

        $users = User::whereDoesntHave('subscriptions', function ($query) {
            $query->where('status', 'active');
        })->get();

        foreach ($users as $user) {
            foreach ($protocols as $protocol) {
                // Send notification to user for each foundational protocol
                Notification::route('mail', $user->email)->notify(new \App\Notifications\ProtocolReminder($protocol));
            }
        }
    }
}
