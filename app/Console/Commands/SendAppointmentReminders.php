<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;
use App\Notifications\Appointment\AppointmentReminder;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    protected $signature = 'app:send-reminders';
    protected $description = 'Send email reminders for appointments scheduled tomorrow';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $this->info("Checking for appointments on: {$tomorrow}");

        $appointments = Appointment::whereDate('appointment_date', $tomorrow)
            ->where('status', AppointmentStatus::Confirmed)
            ->with('user') 
            ->get();

        $this->info("Found {$appointments->count()} appointments.");

        foreach ($appointments as $appt) {
            // Determine Recipient (User or Guest)
            $recipient = $appt->user ?? Notification::route('mail', $appt->patient_email);

            if ($recipient) {
                try {
                    $recipient->notify(new AppointmentReminder($appt));
                    $this->info("Sent reminder to: {$appt->patient_name}");
                } catch (\Exception $e) {
                    $this->error("Failed to send: " . $e->getMessage());
                }
            }
        }
    }
}