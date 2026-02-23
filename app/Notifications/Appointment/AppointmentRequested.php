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

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Broadcast to both Email and the Database (Bell Icon) if registered user
        return $notifiable instanceof \App\Models\User ? ['mail', 'database'] : ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->appointment->appointment_date->format('F d, Y');
        $time = date('h:i A', strtotime($this->appointment->appointment_time));
        $service = $this->appointment->service;

        $message = (new MailMessage)
                    ->subject('Appointment Request Received - ClearOptics')
                    ->greeting("Hello {$this->appointment->patient_name},")
                    ->line("We have received your appointment request for **{$service}** on **{$date}** at **{$time}**.")
                    ->line('Your request is currently pending review by our clinic staff. We will notify you once it has been confirmed.');

        if ($notifiable instanceof \App\Models\User) {
            $message->action('View My Appointments', route('my.appointments'));
        }

        return $message->line('Thank you for choosing ClearOptics!');
    }

    /**
     * Get the array representation of the notification for the Database (Bell Icon).
     */
    public function toArray(object $notifiable): array
    {
        $date = $this->appointment->appointment_date->format('M d, Y');

        return [
            'appointment_id' => $this->appointment->id,
            'status' => 'pending',
            'message' => "Your appointment request for {$date} is pending confirmation.",
            'url' => route('my.appointments') // Redirects to their appointments list
        ];
    }
}