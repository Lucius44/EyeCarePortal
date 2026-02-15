<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\DaySetting;
use App\Models\User;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;

class AppointmentService
{
    /**
     * Generate all necessary calendar data for the frontend.
     */
    public function getCalendarData()
    {
        $appointments = Appointment::where('appointment_date', '>=', Carbon::today())
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->get();

        $dailyCounts = [];
        $takenSlots = [];

        foreach ($appointments as $app) {
            $date = $app->appointment_date->format('Y-m-d');
            
            if (!isset($dailyCounts[$date])) {
                $dailyCounts[$date] = 0;
            }
            $dailyCounts[$date]++;

            if (!isset($takenSlots[$date])) {
                $takenSlots[$date] = [];
            }
            $takenSlots[$date][] = $app->appointment_time;
        }

        $daySettings = DaySetting::where('date', '>=', Carbon::today())
            ->get()
            ->keyBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        $calendarStatus = [];
        $allDates = array_unique(array_merge(array_keys($dailyCounts), $daySettings->keys()->toArray()));

        foreach ($allDates as $date) {
            $setting = $daySettings[$date] ?? null;
            $count = $dailyCounts[$date] ?? 0;
            
            $limit = $setting ? $setting->max_appointments : 5; 
            $isClosed = $setting ? $setting->is_closed : false;

            if ($isClosed) {
                $calendarStatus[$date] = 'closed';
            } elseif ($count >= $limit) {
                $calendarStatus[$date] = 'full';
            } else {
                $calendarStatus[$date] = 'open';
            }
        }

        return [
            'dailyCounts' => $dailyCounts,
            'takenSlots' => $takenSlots,
            'daySettings' => $daySettings,
            'calendarStatus' => $calendarStatus,
        ];
    }

    /**
     * Handle the core logic for creating an appointment.
     * Returns the created Appointment object or throws an exception/error array.
     */
    public function createAppointment(array $data, string $origin = 'patient')
    {
        $date = $data['appointment_date'];
        
        // --- RACE CONDITION PROTECTION ---
        // We lock the specific DATE. This prevents two issues:
        // 1. Double Booking: Two people grabbing 10:00 AM at the exact same moment.
        // 2. Limit Breach: Two people booking DIFFERENT slots, pushing the daily count over the limit.
        // The lock waits 5 seconds to acquire, then releases.
        $lock = Cache::lock("booking_lock_{$date}", 10);

        try {
            return $lock->block(5, function () use ($data, $origin) {
                
                // NOTE: We must perform all checks INSIDE the lock to ensure we are reading
                // the most up-to-date data after the previous person finished.

                $date = $data['appointment_date'];
                $time = $data['appointment_time'];

                // 1. Check Day Settings (Closed / Limits)
                $setting = DaySetting::where('date', $date)->first();

                if ($setting && $setting->is_closed) {
                    return ['error' => 'The clinic is closed on this date.'];
                }

                $limit = $setting ? $setting->max_appointments : 5;

                $countOnDate = Appointment::where('appointment_date', $date)
                    ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
                    ->count();

                if ($countOnDate >= $limit) {
                    return ['error' => "This date is fully booked ({$countOnDate}/{$limit})."];
                }

                // 2. Check Double Booking (Time Slot)
                $isTaken = Appointment::where('appointment_date', $date)
                    ->where('appointment_time', $time)
                    ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
                    ->exists();

                if ($isTaken) {
                    return ['error' => 'The selected time slot is already taken.'];
                }

                // 3. Prepare Data for Insertion
                $appointmentData = [
                    'appointment_date' => $date,
                    'appointment_time' => $time,
                    'service' => $data['service'],
                    'description' => $data['description'] ?? null,
                    // Admin bookings are Confirmed immediately; Patients are Pending
                    'status' => ($origin === 'admin') ? AppointmentStatus::Confirmed : AppointmentStatus::Pending,
                ];

                // 4. Handle User Linking vs Guest
                if (isset($data['user_id'])) {
                    // Logic for Authenticated User (Patient Dashboard)
                    $appointmentData['user_id'] = $data['user_id'];
                } elseif (isset($data['email'])) {
                    // Logic for Admin entering an email (Check if user exists)
                    $existingUser = User::where('email', $data['email'])->first();
                    
                    if ($existingUser) {
                        $appointmentData['user_id'] = $existingUser->id;
                    } else {
                        // Guest / Walk-in
                        $appointmentData['user_id'] = null;
                        $appointmentData['patient_first_name'] = $data['first_name'] ?? null;
                        $appointmentData['patient_middle_name'] = $data['middle_name'] ?? null;
                        $appointmentData['patient_last_name'] = $data['last_name'] ?? null;
                        $appointmentData['patient_email'] = $data['email'] ?? null;
                        $appointmentData['patient_phone'] = $data['phone'] ?? null;
                    }
                }

                // 5. Create
                return Appointment::create($appointmentData);
            });

        } catch (LockTimeoutException $e) {
            // This happens if the server is extremely busy and the user couldn't get a lock
            return ['error' => 'The server is currently busy processing other bookings. Please try again in a moment.'];
        }
    }
}