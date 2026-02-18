<?php

namespace App\Notifications\Appointment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class AppointmentRequested extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via(object $notifiable): array
    {
        // 1. Resolve Email (User vs Guest)
        $email = $notifiable->email ?? $notifiable->routeNotificationFor('mail');

        // 2. Dummy Email Guard
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
            ->subject('Appointment Request Received - EyeCarePortal')
            ->greeting('Hello ' . $this->appointment->patient_name . ',')
            ->line('We have received your appointment request.')
            ->line("**Service:** {$service}")
            ->line("**Date:** {$date} at {$time}")
            ->line('Your request is currently PENDING. We will notify you once an admin confirms your schedule.')
            ->action('View Appointment', url('/appointments'))
            ->line('Thank you for choosing EyeCarePortal!');
    }
}