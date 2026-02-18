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

    public function __construct(bool $isApproved, ?string $reason = null)
    {
        $this->isApproved = $isApproved;
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->greeting('Hello ' . $notifiable->first_name . ',');

        if ($this->isApproved) {
            return $mail
                ->subject('ID Verification Successful ✅ - EyeCarePortal')
                ->line('Great news! Your ID has been verified by our team.')
                ->line('You now have full access to book appointments.')
                ->action('Book an Appointment', url('/appointments'))
                ->line('Thank you for being part of EyeCarePortal.');
        } else {
            return $mail
                ->subject('ID Verification Update - Action Required ⚠️')
                ->error()
                ->line('We could not verify your ID at this time.')
                ->line('**Reason:** ' . ($this->reason ?? 'Document unclear or invalid.'))
                ->line('Please upload a clear, valid ID to proceed.')
                ->action('Upload Again', url('/settings'));
        }
    }
}