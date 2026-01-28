<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use App\Models\DaySetting; // <--- IMPORT THIS
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Http\Resources\CalendarEventResource;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::now('Asia/Manila')->startOfDay();
        $totalPatients = User::where('role', UserRole::Patient)->count();
        
        $appointmentsToday = Appointment::whereDate('appointment_date', $today)
            ->whereIn('status', [AppointmentStatus::Confirmed, AppointmentStatus::Completed])
            ->count();

        $pendingRequests = Appointment::where('status', AppointmentStatus::Pending)->count();
        $totalCompleted = Appointment::where('status', AppointmentStatus::Completed)->count();

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

        return view('admin.dashboard', compact('totalPatients', 'appointmentsToday', 'pendingRequests', 'totalCompleted', 'labels', 'data'));
    }

    public function calendar()
    {
        // 1. Fetch Appointments
        $appointments = Appointment::with('user')
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->get();

        $events = CalendarEventResource::collection($appointments)->resolve();

        // 2. --- NEW: Fetch Day Settings ---
        $daySettings = DaySetting::all()->keyBy(function($item) {
            return $item->date->format('Y-m-d');
        });
        
        // Pass both events and settings to the view
        return view('admin.calendar', compact('events', 'daySettings'));
    }

    // --- NEW: Method to handle the "Day Setting" form from the Modal ---
    public function updateDaySetting(Request $request) 
    {
        $request->validate([
            'date' => 'required|date',
            'max_appointments' => 'required|integer|min:0',
            'is_closed' => 'required|boolean' // 0 or 1
        ]);

        DaySetting::updateOrCreate(
            ['date' => $request->date],
            [
                'max_appointments' => $request->max_appointments,
                'is_closed' => $request->is_closed
            ]
        );

        return back()->with('success', 'Day settings updated successfully.');
    }

    public function storeAppointment(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'service' => 'required|string',
            'description' => 'nullable|string'
        ]);

        // --- NEW: Check Day Settings for Admin too ---
        $date = $request->appointment_date;
        $setting = DaySetting::where('date', $date)->first();

        // Optional: Allow Admin to override "Closed"? 
        // For now, let's warn them or block them. Let's block for consistency.
        if ($setting && $setting->is_closed) {
             return back()->withErrors(['appointment_date' => 'The clinic is marked as CLOSED on this day.'])->withInput();
        }

        $limit = $setting ? $setting->max_appointments : 5;

        $countOnDate = Appointment::where('appointment_date', $request->appointment_date)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->count();

        if ($countOnDate >= $limit) {
            return back()->withErrors(['appointment_date' => "This date is fully booked ({$countOnDate}/{$limit})."])->withInput();
        }

        $isTaken = Appointment::where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $request->appointment_time)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->exists();

        if ($isTaken) {
            return back()->withErrors(['appointment_time' => 'The selected time slot is already taken.'])->withInput();
        }

        $user = User::where('email', $request->email)->first();

        $data = [
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'service' => $request->service,
            'description' => $request->description,
            'status' => AppointmentStatus::Confirmed,
        ];

        if ($user) {
            $data['user_id'] = $user->id;
        } else {
            $data['user_id'] = null;
            $data['patient_first_name'] = $request->first_name;
            $data['patient_middle_name'] = $request->middle_name;
            $data['patient_last_name'] = $request->last_name;
            $data['patient_email'] = $request->email;
            $data['patient_phone'] = $request->phone;
        }

        Appointment::create($data);

        return back()->with('success', 'Appointment booked successfully.');
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

    public function history(Request $request)
    {
        $query = Appointment::with('user')
            ->whereIn('status', [AppointmentStatus::Completed, AppointmentStatus::Cancelled, AppointmentStatus::Rejected, AppointmentStatus::NoShow]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($u) use ($search) {
                    $u->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                })
                ->orWhere('patient_first_name', 'like', "%{$search}%")
                ->orWhere('patient_last_name', 'like', "%{$search}%");
            });
        }

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

    public function users(Request $request)
    {
        $pendingUsers = User::where('role', UserRole::Patient)
                            ->whereNotNull('id_photo_path')
                            ->where('is_verified', false)
                            ->get();

        $query = User::where('role', UserRole::Patient);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_status')) {
            if ($request->filter_status === 'verified') $query->where('is_verified', true);
            elseif ($request->filter_status === 'unverified') $query->where('is_verified', false);
        }

        $allUsers = $query->orderBy('created_at', 'desc')->get();

        $guests = Appointment::whereNull('user_id')
            ->select('patient_first_name', 'patient_last_name', 'patient_email', 'patient_phone', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('patient_email'); 

        return view('admin.users', compact('pendingUsers', 'allUsers', 'guests'));
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