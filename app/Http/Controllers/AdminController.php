<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use App\Models\DaySetting;
use App\Models\Service; 
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Resources\CalendarEventResource;
use App\Services\AppointmentService;
use App\Services\DashboardService; 
use App\Http\Requests\StoreAdminAppointmentRequest;
use App\Http\Requests\UpdateDaySettingRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Appointment\AppointmentStatusChanged;
use App\Notifications\Account\IdVerificationResult;

class AdminController extends Controller
{
    protected $appointmentService;
    protected $dashboardService;

    public function __construct(AppointmentService $appointmentService, DashboardService $dashboardService)
    {
        $this->appointmentService = $appointmentService;
        $this->dashboardService = $dashboardService;
    }

    public function dashboard()
    {
        $stats = $this->dashboardService->getAdminStats();
        return view('admin.dashboard', $stats);
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current records.']);
        }

        $user->update([
            'password' => $request->password, 
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function showUserIdPhoto($id)
    {
        $user = User::findOrFail($id);

        if (!$user->id_photo_path) {
            abort(404, 'ID Photo record not found.');
        }

        if (!Storage::disk('local')->exists($user->id_photo_path)) {
            abort(404, 'File not found in secure storage.');
        }

        $path = Storage::disk('local')->path($user->id_photo_path);

        return response()->file($path);
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
                $affectedAppointments = Appointment::where('appointment_date', $cleanDate)
                    ->where('status', AppointmentStatus::Pending)
                    ->with('user') 
                    ->get();

                foreach ($affectedAppointments as $appt) {
                    $appt->update([
                        'status' => AppointmentStatus::Rejected,
                        'cancellation_reason' => $request->close_reason ?? 'Clinic closed for the day.'
                    ]);

                    // --- NOTIFICATION (Bulk Reject) ---
                    $recipient = $appt->user ?? Notification::route('mail', $appt->patient_email);
                    
                    if ($recipient) {
                         $recipient->notify(new AppointmentStatusChanged($appt));
                    }
                }
            }
        });

        $message = 'Day settings updated successfully.';
        if ($isClosed) {
            $message .= ' Any pending requests for this date have been rejected and notified.';
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
                              ->paginate(10, ['*'], 'pending_page');

        $confirmed = Appointment::where('status', AppointmentStatus::Confirmed)
                                ->with('user')
                                ->orderBy('appointment_date')
                                ->paginate(10, ['*'], 'confirmed_page');
                                
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

        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }

        $history = $query->orderByDesc('appointment_date')->paginate(15)->withQueryString();

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

        if ($request->status === AppointmentStatus::NoShow->value && $appointment->user_id) {
            $this->appointmentService->penalizeUser($appointment->user);
        }

        // --- NOTIFICATION TRIGGER ---
        $recipient = $appointment->user ?? Notification::route('mail', $appointment->patient_email);

        if ($recipient) {
            $recipient->notify(new AppointmentStatusChanged($appointment));
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

        $query = User::where('role', UserRole::Patient);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // --- NEW FILTERING LOGIC ---
        if ($request->filled('filter_status')) {
            if ($request->filter_status === 'active') {
                $query->whereNotNull('email_verified_at')
                      ->where('is_verified', true)
                      ->where('account_status', UserStatus::Active);
            }
            elseif ($request->filter_status === 'pending_email') {
                $query->whereNull('email_verified_at');
            }
            elseif ($request->filter_status === 'pending_id') {
                $query->whereNotNull('email_verified_at')
                      ->where('is_verified', false);
            }
            elseif ($request->filter_status === 'restricted') {
                $query->where('account_status', UserStatus::Restricted);
            }
            elseif ($request->filter_status === 'all') {
                // Do nothing, let query get everyone
            }
        } else {
            // Default View: Hide Unverified Emails
            $query->whereNotNull('email_verified_at');
        }

        $allUsers = $query->orderBy('created_at', 'desc')->paginate(10, ['*'], 'users_page')->withQueryString();

        $restrictedUsers = User::where('role', UserRole::Patient)
                               ->where('account_status', UserStatus::Restricted)
                               ->paginate(10, ['*'], 'restricted_page')
                               ->withQueryString();

        $guests = Appointment::whereNull('user_id')
            ->whereIn('id', function($q) {
                $q->select(DB::raw('MAX(id)'))
                  ->from('appointments')
                  ->whereNull('user_id')
                  ->groupBy('patient_email');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'guests_page'); 

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
            
            // --- NOTIFY APPROVAL ---
            $user->notify(new IdVerificationResult(true));

            return back()->with('success', 'User verified successfully!');
        } 
        
        if ($action === 'reject') {
            $request->validate(['reason' => 'required|string|max:255']);
            
            if($user->id_photo_path && Storage::disk('local')->exists($user->id_photo_path)) {
                Storage::disk('local')->delete($user->id_photo_path);
            }

            $user->update([
                'id_photo_path' => null, 
                'is_verified' => false,
                'rejection_reason' => $request->input('reason')
            ]);

            // --- NOTIFY REJECTION ---
            $user->notify(new IdVerificationResult(false, $request->input('reason')));

            return back()->with('success', 'User verification rejected.');
        }

        return back()->with('error', 'Invalid action.');
    }

    public function unrestrictUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->account_status !== UserStatus::Restricted) {
            return back()->with('error', 'User is not restricted.');
        }

        $user->update([
            'account_status' => UserStatus::Active,
            'strikes' => 0, 
            'restricted_until' => null
        ]);

        return back()->with('success', 'Restriction lifted. User is now Active.');
    }
}