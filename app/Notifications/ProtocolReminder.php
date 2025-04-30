<?php

namespace App\Notifications;

use App\Modules\ContentManagement\Models\Protocol;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProtocolReminder extends Notification
{
    use Queueable;

    private $protocol;

    /**
     * Create a new notification instance.
     */
    public function __construct(Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('Reminder for foundational protocol: ' . $this->protocol->name)
            ->line($this->protocol->description)
            ->action('View Protocol', url('/protocols/' . $this->protocol->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'protocol_id' => $this->protocol->id,
            'protocol_name' => $this->protocol->name,
        ];
    }
}
