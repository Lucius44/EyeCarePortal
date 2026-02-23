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
                    ->subject('Reminder: Upcoming Appointment - ClearOptics')
                    ->greeting("Hello {$this->appointment->patient_name},")
                    ->line("This is a friendly reminder that you have a confirmed appointment for **{$service}** tomorrow, **{$date}** at **{$time}**.")
                    ->line('Please try to arrive 10-15 minutes early.');

        if ($notifiable instanceof \App\Models\User) {
            $message->action('View Appointment Details', route('my.appointments'));
        }

        return $message->line('We look forward to seeing you!');
    }

    /**
     * Get the array representation of the notification for the Database (Bell Icon).
     */
    public function toArray(object $notifiable): array
    {
        $time = date('h:i A', strtotime($this->appointment->appointment_time));

        return [
            'appointment_id' => $this->appointment->id,
            'status' => 'reminder',
            'message' => "Reminder: You have an upcoming appointment tomorrow at {$time}.",
            'url' => route('my.appointments') // Redirects to their appointments list
        ];
    }
}