<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\User;
use App\Enums\AppointmentStatus;
use App\Services\AppointmentService; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index()
    {
        $userId = Auth::id();

        $activeAppointment = Appointment::where('user_id', $userId)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->first();

        $calendarData = $this->appointmentService->getCalendarData();

        $services = Appointment::getServices();
        
        return view('patient.appointments', array_merge(
            [
                'services' => $services, 
                'activeAppointment' => $activeAppointment
            ],
            $calendarData
        ));
    }

    public function store(StoreAppointmentRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // --- RULE 1: Permanent Restriction (3 Strikes) ---
        if ($user->account_status === 'restricted') {
            
            // NEW: Auto-Unrestrict Logic
            // If the user served their time (now > restricted_until), lift the ban.
            if ($user->restricted_until && now()->greaterThanOrEqualTo($user->restricted_until)) {
                $user->update([
                    'account_status' => 'active',
                    'strikes' => 0,
                    'restricted_until' => null
                ]);
                // Allow them to proceed...
            } else {
                // Still restricted? Show the specific date.
                $dateStr = $user->restricted_until ? $user->restricted_until->format('F d, Y') : 'indefinitely';
                return redirect()->back()->withErrors(['error' => "Your account is restricted until {$dateStr} due to multiple violations."]);
            }
        }

        // --- RULE 2: Temporary Timeout (Anti-Spam) ---
        // Check if the user is currently in a "Timeout"
        if ($user->restricted_until && now()->lessThan($user->restricted_until)) {
            // FIX: Use floatDiffInMinutes and ceil() to get a nice whole number (e.g., 4.1 mins -> 5 mins)
            $minutes = (int) ceil(now()->floatDiffInMinutes($user->restricted_until));
            return redirect()->back()->withErrors(['error' => "You are temporarily blocked from booking due to excessive rescheduling. Please try again in {$minutes} minutes."]);
        }

        // --- RULE 3: Detect Spam Behavior (The "Indecision" Check) ---
        // Count how many 'Pending' appointments this user cancelled (soft deleted) in the last 60 minutes
        $spamCount = Appointment::onlyTrashed() 
            ->where('user_id', $user->id)
            ->where('status', AppointmentStatus::Pending) // Only count Pending (Confirmed cancels are handled differently)
            ->where('deleted_at', '>=', now()->subHour())
            ->count();

        if ($spamCount >= 3) {
            // Apply 1-Hour Timeout
            $user->update(['restricted_until' => now()->addHour()]);
            return redirect()->back()->withErrors(['error' => 'You have cancelled too many requests recently. Please wait 1 hour before booking again.']);
        }

        // --- RULE 4: One Active Appointment Limit ---
        $hasActive = Appointment::where('user_id', $user->id)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->exists();

        if ($hasActive) {
            return redirect()->back()->withErrors(['error' => 'You already have an active appointment.']);
        }

        // Proceed with booking
        $data = $request->validated();
        $data['user_id'] = $user->id; 

        $result = $this->appointmentService->createAppointment($data, 'patient');

        if (is_array($result) && isset($result['error'])) {
            return redirect()->back()->withErrors(['error' => $result['error']])->withInput();
        }

        return redirect()->route('appointments.index')->with('success', 'Appointment request submitted successfully!');
    }

    public function cancel(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($appointment->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // SCENARIO A: PENDING (Indecision)
        if ($appointment->status === AppointmentStatus::Pending) {
            $appointment->delete(); 
            return back()->with('success', 'Appointment request removed successfully.');
        }

        // SCENARIO B: CONFIRMED (Commitment)
        if ($appointment->status === AppointmentStatus::Confirmed) {
            $request->validate([
                'cancellation_reason' => 'required|string|max:255',
            ]);

            // Calculate time difference
            $apptDateTime = Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time);
            $hoursUntil = now()->diffInHours($apptDateTime, false);

            $message = 'Appointment cancelled successfully.';

            // Check for Late Cancellation (< 24 Hours)
            if ($hoursUntil < 24) {
                // Apply Penalty
                $user->increment('strikes');
                
                // Check if Limit Reached
                if ($user->strikes >= 3) {
                    // UPDATED: Set 6 Month Restriction
                    $user->update([
                        'account_status' => 'restricted',
                        'restricted_until' => now()->addMonths(6)
                    ]);
                    $message = 'Appointment cancelled. WARNING: Your account has been RESTRICTED for 6 months due to multiple late cancellations.';
                } else {
                    $message = 'Appointment cancelled. You received a STRIKE for cancelling less than 24 hours in advance.';
                }
            }

            $appointment->update([
                'status' => AppointmentStatus::Cancelled,
                'cancellation_reason' => $request->cancellation_reason
            ]);
            
            return back()->with('success', $message);
        }

        return back()->with('error', 'This appointment cannot be cancelled.');
    }
}