<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class NewAppointmentRequest extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Admin UI only
    }

    public function toArray(object $notifiable): array
    {
        $patientName = $this->appointment->patient_name;
        $date = $this->appointment->appointment_date->format('M d');
        
        return [
            'type' => 'appointment_request',
            'message' => "New request from {$patientName} for {$date}.",
            'url' => route('admin.appointments') 
        ];
    }
}