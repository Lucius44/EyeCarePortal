<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Http\Resources\CalendarEventResource;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // --- NEW: The Stats Dashboard ---
    public function dashboard()
    {
        // 1. Basic Counters
        $totalPatients = User::where('role', UserRole::Patient)->count();
        $appointmentsToday = Appointment::whereDate('appointment_date', Carbon::today())->count();
        $pendingRequests = Appointment::where('status', AppointmentStatus::Pending)->count();
        $totalCompleted = Appointment::where('status', AppointmentStatus::Completed)->count();

        // 2. Chart Data: Appointments per day (Last 7 Days)
        // This query groups appointments by date and counts them
        $chartData = Appointment::select(DB::raw('DATE(appointment_date) as date'), DB::raw('count(*) as count'))
            ->where('appointment_date', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Prepare arrays for Chart.js
        $labels = [];
        $data = [];
        
        // Loop last 7 days to ensure even days with 0 appointments show up
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('M d'); // e.g. "Jan 25"
            
            // Find count for this date or default to 0
            $record = $chartData->firstWhere('date', $date);
            $data[] = $record ? $record->count : 0;
        }

        return view('admin.dashboard', compact(
            'totalPatients', 
            'appointmentsToday', 
            'pendingRequests', 
            'totalCompleted',
            'labels',
            'data'
        ));
    }

    // --- RENAMED: Old Dashboard is now Calendar ---
    public function calendar()
    {
        $appointments = Appointment::with('user')->get();
        $events = CalendarEventResource::collection($appointments)->resolve();
        return view('admin.calendar', compact('events'));
    }

    public function appointments()
    {
        $pending = Appointment::where('status', AppointmentStatus::Pending)
                              ->with('user')
                              ->orderBy('appointment_date')
                              ->get();

        $confirmed = Appointment::where('status', AppointmentStatus::Confirmed)
                                ->with('user')
                                ->orderBy('appointment_date')
                                ->get();
                                
        return view('admin.appointments', compact('pending', 'confirmed'));
    }

    public function history()
    {
        $history = Appointment::whereIn('status', [
                                    AppointmentStatus::Completed, 
                                    AppointmentStatus::Cancelled, 
                                    AppointmentStatus::Rejected,
                                    AppointmentStatus::NoShow
                                ])
                              ->with('user')
                              ->orderByDesc('appointment_date')
                              ->get();

        return view('admin.history', compact('history'));
    }

    public function updateStatus(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        $request->validate([
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
            'cancellation_reason' => 'nullable|string|max:500', 
        ]);

        $data = ['status' => $request->status];

        if ($request->status === AppointmentStatus::Rejected->value) {
            $data['cancellation_reason'] = $request->input('cancellation_reason', 'No reason provided.');
        }

        $appointment->update($data);

        return back()->with('success', 'Appointment status updated successfully.');
    }

    public function users()
    {
        $pendingUsers = User::where('role', UserRole::Patient)
                            ->whereNotNull('id_photo_path')
                            ->where('is_verified', false)
                            ->get();

        $allUsers = User::where('role', UserRole::Patient)->get();

        return view('admin.users', compact('pendingUsers', 'allUsers'));
    }

    public function verifyUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $action = $request->input('action'); 

        if ($action === 'approve') {
            $user->update(['is_verified' => true]);
            return back()->with('success', 'User verified successfully!');
        } 
        
        if ($action === 'reject') {
            $user->update([
                'id_photo_path' => null, 
                'is_verified' => false
            ]);
            return back()->with('success', 'User verification rejected. They can upload a new ID.');
        }

        return back()->with('error', 'Invalid action.');
    }
}