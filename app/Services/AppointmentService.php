<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\DaySetting;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;

class AppointmentService
{
    /**
     * Generate all necessary calendar data for the frontend.
     * Returns: dailyCounts, takenSlots, calendarStatus
     */
    public function getCalendarData()
    {
        // 1. Fetch future appointments
        $appointments = Appointment::where('appointment_date', '>=', Carbon::today())
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->get();

        $dailyCounts = [];
        $takenSlots = [];

        // 2. Process Appointments
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

        // 3. Fetch Day Settings (Closures/Limits)
        $daySettings = DaySetting::where('date', '>=', Carbon::today())
            ->get()
            ->keyBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        // 4. Calculate Status (Open/Full/Closed)
        $calendarStatus = [];
        $allDates = array_unique(array_merge(array_keys($dailyCounts), $daySettings->keys()->toArray()));

        foreach ($allDates as $date) {
            $setting = $daySettings[$date] ?? null;
            $count = $dailyCounts[$date] ?? 0;
            
            $limit = $setting ? $setting->max_appointments : 5; // Default 5
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
            'daySettings' => $daySettings, // Passed in case view needs raw settings
            'calendarStatus' => $calendarStatus,
        ];
    }
}