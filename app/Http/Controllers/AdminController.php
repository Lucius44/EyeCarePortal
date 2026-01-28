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
    // --- Stats Dashboard ---
    public function dashboard()
    {
        $totalPatients = User::where('role', UserRole::Patient)->count();
        $appointmentsToday = Appointment::whereDate('appointment_date', Carbon::today())->count();
        $pendingRequests = Appointment::where('status', AppointmentStatus::Pending)->count();
        $totalCompleted = Appointment::where('status', AppointmentStatus::Completed)->count();

        $chartData = Appointment::select(DB::raw('DATE(appointment_date) as date'), DB::raw('count(*) as count'))
            ->where('appointment_date', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('M d'); 
            
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

    // --- Calendar ---
    public function calendar()
    {
        $appointments = Appointment::with('user')
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->get();

        $events = CalendarEventResource::collection($appointments)->resolve();
        
        return view('admin.calendar', compact('events'));
    }

    // --- Manage Appointments ---
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

    // --- UPDATED: History with Search & Filter ---
    public function history(Request $request)
    {
        $query = Appointment::with('user')
            ->whereIn('status', [
                AppointmentStatus::Completed, 
                AppointmentStatus::Cancelled, 
                AppointmentStatus::Rejected,
                AppointmentStatus::NoShow
            ]);

        // 1. Search by Patient Name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // 2. Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $history = $query->orderByDesc('appointment_date')->get();

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

    // --- UPDATED: Users with Search & Filter ---
    public function users(Request $request)
    {
        // Keep pending separate (always show these)
        $pendingUsers = User::where('role', UserRole::Patient)
                            ->whereNotNull('id_photo_path')
                            ->where('is_verified', false)
                            ->get();

        // Main User Query
        $query = User::where('role', UserRole::Patient);

        // 1. Search by Name or Email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 2. Filter by Verification Status
        if ($request->filled('filter_status')) {
            if ($request->filter_status === 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->filter_status === 'unverified') {
                $query->where('is_verified', false);
            }
        }

        $allUsers = $query->orderBy('created_at', 'desc')->get();

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