<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class AppointmentCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $date = $this->appointment->appointment_date->format('M d, Y');
        $time = date('h:i A', strtotime($this->appointment->appointment_time));

        return [
            'type' => 'appointment_cancelled',
            'status' => 'cancelled', // Added for UI helper tracking
            'message' => "Appointment on {$date} at {$time} was CANCELLED by the patient.",
            'url' => route('admin.calendar') // Directs admin to the calendar to see the open slot
        ];
    }
}