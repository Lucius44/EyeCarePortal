<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to ClearOptics! Please Verify Your Email')
            ->from(config('mail.from.address'), 'ClearOptics') 
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Thank you for registering at ClearOptics. To complete your registration and verify your email address, please enter the following 6-digit code in the portal:')
            ->line('**' . $notifiable->email_otp . '**')
            ->line('This code will expire in 15 minutes.')
            ->line('If you did not create an account, no further action is required.');
    }
}