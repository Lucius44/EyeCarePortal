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
     * Check if a patient is allowed to book.
     * Returns TRUE if allowed, or an ARRAY ['error' => message] if blocked.
     */
    public function checkPatientEligibility(User $user)
    {
        // --- RULE 1: Permanent Restriction (3 Strikes) ---
        if ($user->account_status === 'restricted') {
            
            // Auto-Unrestrict Logic
            if ($user->restricted_until && now()->greaterThanOrEqualTo($user->restricted_until)) {
                $user->update([
                    'account_status' => 'active',
                    'strikes' => 0,
                    'restricted_until' => null
                ]);
            } else {
                $dateStr = $user->restricted_until ? $user->restricted_until->format('F d, Y') : 'indefinitely';
                return ['error' => "Your account is restricted until {$dateStr} due to multiple violations."];
            }
        }

        // --- RULE 2: Temporary Timeout (Anti-Spam) ---
        if ($user->restricted_until && now()->lessThan($user->restricted_until)) {
            $minutes = (int) ceil(now()->floatDiffInMinutes($user->restricted_until));
            return ['error' => "You are temporarily blocked from booking due to excessive rescheduling. Please try again in {$minutes} minutes."];
        }

        // --- RULE 3: Detect Spam Behavior ---
        $spamCount = Appointment::onlyTrashed() 
            ->where('user_id', $user->id)
            ->where('status', AppointmentStatus::Pending)
            ->where('deleted_at', '>=', now()->subHour())
            ->count();

        if ($spamCount >= 3) {
            $user->update(['restricted_until' => now()->addHour()]);
            return ['error' => 'You have cancelled too many requests recently. Please wait 1 hour before booking again.'];
        }

        // --- RULE 4: One Active Appointment Limit ---
        $hasActive = Appointment::where('user_id', $user->id)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->exists();

        if ($hasActive) {
            return ['error' => 'You already have an active appointment.'];
        }

        return true;
    }

    /**
     * Apply a strike to a user and check for restriction thresholds.
     * Returns TRUE if the user was just restricted, FALSE otherwise.
     */
    public function penalizeUser(User $user)
    {
        $user->increment('strikes');

        // Check if Limit Reached (3 Strikes)
        if ($user->strikes >= 3) {
            // UPDATED: Changed from 6 months to 30 days
            $user->update([
                'account_status' => 'restricted',
                'restricted_until' => now()->addDays(30)
            ]);
            return true; // User was Restricted
        }

        return false; // User was just Warned
    }

    /**
     * Handle the core logic for creating an appointment.
     */
    public function createAppointment(array $data, string $origin = 'patient')
    {
        $date = $data['appointment_date'];
        
        $lock = Cache::lock("booking_lock_{$date}", 10);

        try {
            return $lock->block(5, function () use ($data, $origin) {
                
                $date = $data['appointment_date'];
                $time = $data['appointment_time'];

                // 1. Check Day Settings
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

                // 2. Check Double Booking
                $isTaken = Appointment::where('appointment_date', $date)
                    ->where('appointment_time', $time)
                    ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
                    ->exists();

                if ($isTaken) {
                    return ['error' => 'The selected time slot is already taken.'];
                }

                // 3. Prepare Data
                $appointmentData = [
                    'appointment_date' => $date,
                    'appointment_time' => $time,
                    'service' => $data['service'],
                    'description' => $data['description'] ?? null,
                    'status' => ($origin === 'admin') ? AppointmentStatus::Confirmed : AppointmentStatus::Pending,
                ];

                // 4. Handle User Linking
                if (isset($data['user_id'])) {
                    $appointmentData['user_id'] = $data['user_id'];
                } elseif (isset($data['email'])) {
                    $existingUser = User::where('email', $data['email'])->first();
                    
                    if ($existingUser) {
                        $appointmentData['user_id'] = $existingUser->id;
                    } else {
                        $appointmentData['user_id'] = null;
                        $appointmentData['patient_first_name'] = $data['first_name'] ?? null;
                        $appointmentData['patient_middle_name'] = $data['middle_name'] ?? null;
                        $appointmentData['patient_last_name'] = $data['last_name'] ?? null;
                        $appointmentData['patient_email'] = $data['email'] ?? null;
                        $appointmentData['patient_phone'] = $data['phone'] ?? null;
                    }
                }

                return Appointment::create($appointmentData);
            });

        } catch (LockTimeoutException $e) {
            return ['error' => 'The server is currently busy processing other bookings. Please try again in a moment.'];
        }
    }
}