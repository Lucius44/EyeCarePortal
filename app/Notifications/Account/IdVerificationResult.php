<?php

namespace App\Notifications\Account;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IdVerificationResult extends Notification implements ShouldQueue
{
    use Queueable;

    protected $isApproved;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(bool $isApproved, string $reason = null)
    {
        $this->isApproved = $isApproved;
        $this->reason = $reason;
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
        $message = (new MailMessage)
                    ->subject($this->isApproved ? 'ID Verification Approved - ClearOptics' : 'ID Verification Rejected - ClearOptics')
                    ->greeting("Hello {$notifiable->first_name},");

        if ($this->isApproved) {
            $message->line('Great news! Your uploaded ID has been verified successfully.')
                    ->line('You now have full access to book appointments through our portal.')
                    ->action('Book an Appointment', route('appointments.index'));
        } else {
            $message->line('We encountered an issue verifying your uploaded ID.')
                    ->line('Reason for rejection: ' . ($this->reason ?? 'Image unclear or invalid ID.'))
                    ->line('Please upload a clear, valid ID to continue using our services.')
                    ->action('Upload New ID', route('settings'));
        }

        return $message->line('Thank you for choosing ClearOptics!');
    }

    /**
     * Get the array representation of the notification for the Database (Bell Icon).
     */
    public function toArray(object $notifiable): array
    {
        if ($this->isApproved) {
            return [
                'status' => 'approved',
                'message' => 'Your ID verification was successful. You can now book appointments.',
                'url' => route('appointments.index') // Redirects to booking page
            ];
        }

        return [
            'status' => 'rejected',
            'message' => 'Your ID verification failed. Please check your settings to upload a new ID.',
            'url' => route('settings') // Redirects back to settings so they can re-upload
        ];
    }
}