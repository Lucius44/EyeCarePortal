<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Retrieve all statistical data required for the Admin Dashboard.
     *
     * @param string $range (7_days, 30_days, 12_months)
     * @return array
     */
    public function getAdminStats(string $range = '7_days'): array
    {
        $today = Carbon::now('Asia/Manila')->startOfDay();
        
        // 1. Basic Counters
        $totalPatients = User::where('role', UserRole::Patient)->count();
        
        $appointmentsToday = Appointment::whereDate('appointment_date', $today)
            ->whereIn('status', [AppointmentStatus::Confirmed, AppointmentStatus::Completed])
            ->count();

        $pendingRequests = Appointment::where('status', AppointmentStatus::Pending)->count();
        
        $pendingVerifications = User::where('role', UserRole::Patient)
            ->whereNotNull('id_photo_path')
            ->where('is_verified', false)
            ->whereNull('rejection_reason')
            ->count();

        $totalCompleted = Appointment::where('status', AppointmentStatus::Completed)->count();

        // 2. Chart Data Generation (Grouped in PHP for cross-database compatibility)
        $labels = [];
        $completedData = [];
        $upcomingData = [];
        $missedData = [];

        if ($range === '12_months') {
            // MONTHLY GROUPING (Past 12 Months)
            $startDate = Carbon::now('Asia/Manila')->startOfMonth()->subMonths(11);
            
            $appointments = Appointment::select('appointment_date', 'status')
                ->where('appointment_date', '>=', $startDate)
                ->get();

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now('Asia/Manila')->subMonths($i);
                $labels[] = $date->format('M Y');
                
                // Filter collection by month-year
                $periodAppointments = $appointments->filter(function($appt) use ($date) {
                    return $appt->appointment_date->format('Y-m') === $date->format('Y-m');
                });

                $this->extractData($periodAppointments, $completedData, $upcomingData, $missedData);
            }
        } else {
            // DAILY GROUPING (7 or 30 days)
            $days = $range === '30_days' ? 30 : 7;
            $startDate = Carbon::now('Asia/Manila')->startOfDay()->subDays($days - 1);
            
            $appointments = Appointment::select('appointment_date', 'status')
                ->where('appointment_date', '>=', $startDate)
                ->get();

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now('Asia/Manila')->subDays($i);
                $labels[] = $date->format('M d'); // e.g. "Oct 12"
                
                // Filter collection by exact date
                $periodAppointments = $appointments->filter(function($appt) use ($date) {
                    return $appt->appointment_date->format('Y-m-d') === $date->format('Y-m-d');
                });

                $this->extractData($periodAppointments, $completedData, $upcomingData, $missedData);
            }
        }

        return [
            'totalPatients' => $totalPatients,
            'appointmentsToday' => $appointmentsToday,
            'pendingRequests' => $pendingRequests,
            'pendingVerifications' => $pendingVerifications,
            'totalCompleted' => $totalCompleted,
            'labels' => $labels,
            'completedData' => $completedData,
            'upcomingData' => $upcomingData,
            'missedData' => $missedData,
        ];
    }

    /**
     * Helper to map enum statuses into distinct arrays for Chart.js
     */
    private function extractData($appointments, &$completedData, &$upcomingData, &$missedData)
    {
        $completed = 0;
        $upcoming = 0;
        $missed = 0;

        foreach ($appointments as $record) {
            // Handle Enum casting natively
            $status = $record->status instanceof AppointmentStatus ? $record->status->value : $record->status;
            
            if ($status === AppointmentStatus::Completed->value) {
                $completed++;
            } elseif (in_array($status, [AppointmentStatus::Pending->value, AppointmentStatus::Confirmed->value])) {
                $upcoming++;
            } elseif (in_array($status, [AppointmentStatus::Cancelled->value, AppointmentStatus::Rejected->value, AppointmentStatus::NoShow->value])) {
                $missed++;
            }
        }

        $completedData[] = $completed;
        $upcomingData[] = $upcoming;
        $missedData[] = $missed;
    }
}