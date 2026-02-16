<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use App\Models\DaySetting;
use App\Models\Service; 
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Http\Resources\CalendarEventResource;
use App\Services\AppointmentService;
use App\Http\Requests\StoreAdminAppointmentRequest;
use App\Http\Requests\UpdateDaySettingRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

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
        $appointments = Appointment::with('user')
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->get();

        $events = CalendarEventResource::collection($appointments)->resolve();

        $daySettings = DaySetting::all()->keyBy(function($item) {
            return $item->date->format('Y-m-d');
        });
        
        $services = Service::all()->sortBy(function($service) {
            return $service->name === 'Others' ? 'ZZZZ' : $service->name;
        });
        
        return view('admin.calendar', compact('events', 'daySettings', 'services'));
    }

    public function updateDaySetting(UpdateDaySettingRequest $request) 
    {
        $cleanDate = Carbon::parse($request->date)->format('Y-m-d');
        $isClosed = $request->boolean('is_closed');

        DB::transaction(function () use ($cleanDate, $request, $isClosed) {
            
            DaySetting::updateOrCreate(
                ['date' => $cleanDate],
                [
                    'max_appointments' => $request->max_appointments,
                    'is_closed' => $isClosed
                ]
            );

            if ($isClosed) {
                Appointment::where('appointment_date', $cleanDate)
                    ->where('status', AppointmentStatus::Pending)
                    ->update([
                        'status' => AppointmentStatus::Rejected,
                        'cancellation_reason' => $request->close_reason ?? 'Clinic closed for the day.'
                    ]);
            }
        });

        $message = 'Day settings updated successfully.';
        if ($isClosed) {
            $message .= ' Any pending requests for this date have been rejected.';
        }

        return back()->with('success', $message);
    }

    public function storeAppointment(StoreAdminAppointmentRequest $request)
    {
        $result = $this->appointmentService->createAppointment($request->validated(), 'admin');

        if (is_array($result) && isset($result['error'])) {
            return back()->withErrors(['appointment_date' => $result['error']])->withInput();
        }

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
                      ->orWhere('middle_name', 'like', "%{$search}%")
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
            'diagnosis' => 'nullable|string|max:1000',
            'prescription' => 'nullable|string|max:1000',
        ]);

        $data = ['status' => $request->status];

        if ($request->status === AppointmentStatus::Rejected->value) {
            $data['cancellation_reason'] = $request->input('cancellation_reason', 'No reason provided.');
        }

        if ($request->status === AppointmentStatus::Completed->value) {
            $data['diagnosis'] = $request->input('diagnosis');
            $data['prescription'] = $request->input('prescription');
        }

        $appointment->update($data);

        // PENALTY LOGIC: Delegated to Service
        if ($request->status === AppointmentStatus::NoShow->value && $appointment->user_id) {
            $this->appointmentService->penalizeUser($appointment->user);
        }

        return back()->with('success', 'Appointment updated successfully.');
    }

    public function users(Request $request)
    {
        $pendingUsers = User::where('role', UserRole::Patient)
                            ->whereNotNull('id_photo_path')
                            ->where('is_verified', false)
                            ->whereNull('rejection_reason')
                            ->get();

        // 1. Fetch ALL Registered Patients
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
            if ($request->filter_status === 'verified') $query->where('is_verified', true)->where('account_status', 'active');
            elseif ($request->filter_status === 'unverified') $query->where('is_verified', false);
            elseif ($request->filter_status === 'restricted') $query->where('account_status', 'restricted'); 
        }

        $allUsers = $query->orderBy('created_at', 'desc')->get();

        // 2. Fetch RESTRICTED Users
        $restrictedUsers = User::where('role', UserRole::Patient)
                               ->where('account_status', 'restricted')
                               ->get();

        // 3. Fetch Walk-in Guests
        $guests = Appointment::whereNull('user_id')
            ->select('patient_first_name', 'patient_middle_name', 'patient_last_name', 'patient_email', 'patient_phone', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('patient_email'); 

        return view('admin.users', compact('pendingUsers', 'allUsers', 'restrictedUsers', 'guests'));
    }

    public function verifyUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $action = $request->input('action'); 

        if ($action === 'approve') {
            $user->update([
                'is_verified' => true,
                'rejection_reason' => null
            ]);
            return back()->with('success', 'User verified successfully!');
        } 
        
        if ($action === 'reject') {
            $request->validate(['reason' => 'required|string|max:255']);
            
            $user->update([
                'id_photo_path' => null, 
                'is_verified' => false,
                'rejection_reason' => $request->input('reason')
            ]);
            return back()->with('success', 'User verification rejected.');
        }

        return back()->with('error', 'Invalid action.');
    }

    public function unrestrictUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->account_status !== 'restricted') {
            return back()->with('error', 'User is not restricted.');
        }

        $user->update([
            'account_status' => 'active',
            'strikes' => 0, 
            'restricted_until' => null
        ]);

        return back()->with('success', 'Restriction lifted. User is now Active.');
    }
}