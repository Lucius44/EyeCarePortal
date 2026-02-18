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
        $status = $this->appointment->status;
        $date = $this->appointment->appointment_date->format('F j, Y');
        $time = date('g:i A', strtotime($this->appointment->appointment_time));

        $mail = (new MailMessage)->greeting('Hello ' . $this->appointment->patient_name . ',');

        switch ($status) {
            case AppointmentStatus::Confirmed:
                $mail->subject('Appointment Confirmed ✅ - EyeCarePortal')
                     ->line('Good news! Your appointment has been confirmed.')
                     ->line("**When:** {$date} at {$time}")
                     ->line('Please arrive 10 minutes early.');
                break;

            case AppointmentStatus::Rejected:
                $reason = $this->appointment->cancellation_reason ?? 'Slot unavailable.';
                $mail->subject('Appointment Update - EyeCarePortal')
                     ->error()
                     ->line('We are sorry, but we cannot accommodate your appointment request.')
                     ->line("**Reason:** {$reason}")
                     ->line('Please try booking a different time slot.');
                break;

            case AppointmentStatus::Cancelled:
                $mail->subject('Appointment Cancelled - EyeCarePortal')
                     ->line('Your appointment has been successfully cancelled.')
                     ->line('We hope to see you again soon.');
                break;

            case AppointmentStatus::Completed:
                $mail->subject('Thank You for Visiting! 👓 - EyeCarePortal')
                     ->line('Your appointment has been marked as completed.')
                     ->line('Thank you for trusting us with your eye care needs.');
                break;

            case AppointmentStatus::NoShow:
                $mail->subject('Missed Appointment - EyeCarePortal')
                     ->error()
                     ->line('We missed you at your scheduled appointment today.')
                     ->line('Please note that multiple no-shows may affect your ability to book future appointments.');
                break;
                
            default:
                $mail->subject('Appointment Status Update')
                     ->line("Your appointment status is now: {$status->value}");
        }

        return $mail->action('Go to Portal', url('/'));
    }
}