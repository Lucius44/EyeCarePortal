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
     * @return array
     */
    public function getAdminStats(): array
    {
        $today = Carbon::now('Asia/Manila')->startOfDay();
        
        // 1. Basic Counters
        $totalPatients = User::where('role', UserRole::Patient)->count();
        
        $appointmentsToday = Appointment::whereDate('appointment_date', $today)
            ->whereIn('status', [AppointmentStatus::Confirmed, AppointmentStatus::Completed])
            ->count();

        $pendingRequests = Appointment::where('status', AppointmentStatus::Pending)->count();
        $totalCompleted = Appointment::where('status', AppointmentStatus::Completed)->count();

        // 2. Chart Data Generation (Last 7 Days)
        $chartData = Appointment::select(DB::raw('DATE(appointment_date) as date'), DB::raw('count(*) as count'))
            ->where('appointment_date', '>=', Carbon::now('Asia/Manila')->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Manila')->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now('Asia/Manila')->subDays($i)->format('M d'); 
            $record = $chartData->firstWhere('date', $date);
            $data[] = $record ? $record->count : 0;
        }

        return [
            'totalPatients' => $totalPatients,
            'appointmentsToday' => $appointmentsToday,
            'pendingRequests' => $pendingRequests,
            'totalCompleted' => $totalCompleted,
            'labels' => $labels,
            'data' => $data,
        ];
    }
}