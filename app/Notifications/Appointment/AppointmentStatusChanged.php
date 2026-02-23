<?php

namespace App\Notifications\Appointment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;

class AppointmentStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // If the patient is a registered user, send both Mail and Database notification.
        // If they are a guest (Anonymous Notifiable), only send Mail.
        return $notifiable instanceof \App\Models\User ? ['mail', 'database'] : ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->appointment->status->value;
        $date = $this->appointment->appointment_date->format('F d, Y');
        $time = date('h:i A', strtotime($this->appointment->appointment_time));
        $service = $this->appointment->service;

        $message = (new MailMessage)
                    ->subject("Appointment {$status} - ClearOptics")
                    ->greeting("Hello {$this->appointment->patient_name},")
                    ->line("Your appointment for {$service} on {$date} at {$time} has been marked as: **{$status}**.");

        if ($this->appointment->status === AppointmentStatus::Rejected || $this->appointment->status === AppointmentStatus::Cancelled) {
            $message->line("Reason: " . ($this->appointment->cancellation_reason ?? 'No reason provided.'));
        }

        // Only add the button if they are a registered user
        if ($notifiable instanceof \App\Models\User) {
            $message->action('View My Appointments', route('my.appointments'));
        }

        return $message->line('Thank you for choosing ClearOptics!');
    }

    /**
     * Get the array representation of the notification for the Database.
     */
    public function toArray(object $notifiable): array
    {
        $status = $this->appointment->status->value;
        $date = $this->appointment->appointment_date->format('M d, Y');
        
        $message = "Your appointment on {$date} has been {$status}.";
        
        if ($this->appointment->status === AppointmentStatus::Rejected) {
             $message = "Your appointment on {$date} was rejected.";
        }

        return [
            'appointment_id' => $this->appointment->id,
            'status' => $status,
            'message' => $message,
            'url' => route('my.appointments') // Where they go when they click the notification
        ];
    }
}