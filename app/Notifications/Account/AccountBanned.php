<?php

namespace App\Notifications\Account;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountBanned extends Notification implements ShouldQueue
{
    use Queueable;

    public $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $reason)
    {
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // For a ban, we only need to send an email because the user can no longer log in to see the bell icon.
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Account Permanently Deactivated - ClearOptics')
                    ->greeting("Hello {$notifiable->first_name},")
                    ->line('We are writing to inform you that your EyeCarePortal account has been permanently deactivated.')
                    ->line('Reason: ' . $this->reason)
                    ->line('As a result, you will no longer be able to log in to the portal or book appointments online.')
                    ->line('Please note that your historical medical records and prescriptions remain securely on file at the clinic. If you need access to these records, please contact our staff directly via phone or in-person.')
                    ->action('Return to Homepage', url('/'))
                    ->line('If you believe this was a mistake, please reach out to the clinic directly.');
    }

    /**
     * Get the array representation of the notification for the Database (Admin Audit purposes).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'status' => 'rejected', // 'rejected' turns the icon red via the app.blade.php helper
            'message' => 'Your account has been PERMANENTLY BANNED. Reason: ' . $this->reason,
            'url' => url('/')
        ];
    }
}