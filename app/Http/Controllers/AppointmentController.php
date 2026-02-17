<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Service; // Imported Service
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

        // FIXED: Fetch services and move "Others" to the bottom
        $services = Service::pluck('name')->sortBy(function ($name) {
            return $name === 'Others' ? 1 : 0; // 1 moves to bottom, 0 stays top
        })->values(); // Reset keys
        
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

        // --- CHECK ELIGIBILITY ---
        $eligibility = $this->appointmentService->checkPatientEligibility($user);

        if ($eligibility !== true) {
            return redirect()->back()->withErrors($eligibility);
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
                // Apply Penalty via Service
                $isRestricted = $this->appointmentService->penalizeUser($user);
                
                if ($isRestricted) {
                    // UPDATED: Changed message to reflect 30 days
                    $message = 'Appointment cancelled. WARNING: Your account has been RESTRICTED for 30 days due to multiple late cancellations.';
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