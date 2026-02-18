<?php

namespace App\Notifications\Appointment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class AppointmentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via(object $notifiable): array
    {
        $email = $notifiable->email ?? $notifiable->routeNotificationFor('mail');

        if (!$email || str_contains($email, 'guest.eyecareportal.com') || str_contains($email, 'no-email')) {
            return [];
        }

        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->appointment->appointment_date->format('F j, Y');
        $time = date('g:i A', strtotime($this->appointment->appointment_time));
        $service = $this->appointment->service;

        return (new MailMessage)
            ->subject('Reminder: Appointment Tomorrow 🗓️ - EyeCarePortal')
            ->greeting('Hello ' . $this->appointment->patient_name . ',')
            ->line('This is a friendly reminder about your upcoming appointment.')
            ->line("**Service:** {$service}")
            ->line("**When:** Tomorrow, {$date} at {$time}")
            ->line('Please arrive 10 minutes early.')
            ->line('If you need to reschedule, please do so at least 24 hours in advance.')
            ->action('View Details', url('/appointments'));
    }
}