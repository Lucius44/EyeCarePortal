<?php

namespace App\Notifications\Account;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountUnrestricted extends Notification implements ShouldQueue
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
            ->subject('Your Account Access is Restored - ClearOptics')
            ->from(config('mail.from.address'), 'ClearOptics')
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('Great news! The restriction on your ClearOptics account has been lifted.')
            ->line('Your strike count has been reset to zero. You are now fully authorized to book new appointments through our portal.')
            ->action('Book an Appointment', route('patient.appointments'))
            ->line('We look forward to seeing you at the clinic!');
    }
}