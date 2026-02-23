<?php

namespace App\Notifications\Account;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountUnrestricted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Broadcast to both Email and the new in-app Notification Bell
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Account Restriction Lifted - ClearOptics')
                    ->greeting("Hello {$notifiable->first_name},")
                    ->line('Good news! The restriction on your account has been lifted.')
                    ->line('Your strikes have been reset, and you may now resume booking appointments.')
                    ->action('Go to Dashboard', route('dashboard'))
                    ->line('Thank you for your cooperation!');
    }

    /**
     * Get the array representation of the notification for the Database (Bell Icon).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'status' => 'unrestricted',
            'message' => 'Your account restriction has been lifted. You can book appointments again.',
            'url' => route('dashboard') // Redirects to their main dashboard
        ];
    }
}