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
        // Added 'mail' channel
        return ['database', 'mail']; 
    }

    public function toMail(object $notifiable): MailMessage
    {
        $patientName = $this->appointment->patient_name;
        $date = $this->appointment->appointment_date->format('F d, Y');
        $time = date('h:i A', strtotime($this->appointment->appointment_time));
        $service = $this->appointment->service ?? 'General Checkup';

        return (new MailMessage)
                    ->subject('New Appointment Request - ClearOptics Admin')
                    ->greeting('Hello Admin,')
                    ->line("You have a new appointment request from **{$patientName}**.")
                    ->line("Requested Date: **{$date}** at **{$time}**")
                    ->line("Service: **{$service}**")
                    ->line('Please review and confirm or reject this request in the admin panel.')
                    ->action('View Appointments', route('admin.appointments'));
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