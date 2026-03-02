<?php

namespace App\Notifications\Account;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class AccountRestricted extends Notification implements ShouldQueue
{
    use Queueable;

    public $reason;
    public $restrictedUntil;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $reason, $restrictedUntil)
    {
        $this->reason = $reason;
        $this->restrictedUntil = $restrictedUntil;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Broadcast to both Email and the in-app Notification Bell
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $date = Carbon::parse($this->restrictedUntil)->format('F j, Y');

        return (new MailMessage)
                    ->subject('Account Restricted - ClearOptics')
                    ->greeting("Hello {$notifiable->first_name},")
                    ->line('We are writing to inform you that your online booking privileges have been temporarily restricted.')
                    ->line('Reason: ' . $this->reason)
                    ->line('This restriction will be automatically lifted on: ' . $date)
                    ->line('During this time, you may still log in to view your medical history, but you will not be able to book new appointments online. Please contact the clinic directly to schedule a visit.')
                    ->action('View Dashboard', route('dashboard'))
                    ->line('Thank you for your understanding.');
    }

    /**
     * Get the array representation of the notification for the Database (Bell Icon).
     */
    public function toArray(object $notifiable): array
    {
        $date = Carbon::parse($this->restrictedUntil)->format('M j, Y');

        return [
            // Using 'restricted' matches the helper in app.blade.php to turn the icon red
            'status' => 'restricted',
            'message' => 'Your account has been RESTRICTED until ' . $date . '. Reason: ' . $this->reason,
            'url' => route('dashboard')
        ];
    }
}