<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Service;
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Services\AppointmentService; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // <--- NEW: Import the PDF Facade

// Notification Classes
use App\Notifications\Appointment\AppointmentRequested;
use App\Notifications\Appointment\AppointmentStatusChanged;
use App\Notifications\Admin\NewAppointmentRequest; 
use App\Notifications\Admin\AppointmentCancelled; 

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

        // 1. Fetch Active Appointment (CRITICAL: Required by view)
        $activeAppointment = Appointment::where('user_id', $userId)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->first();

        // 2. Fetch Calendar Data via Service 
        $calendarData = $this->appointmentService->getCalendarData();

        // 3. Fetch Services
        $services = Service::pluck('name')->sortBy(function ($name) {
            return $name === 'Others' ? 1 : 0; 
        })->values(); 
        
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

        // --- NOTIFICATIONS ---
        if ($result instanceof Appointment) {
            // 1. Notify Patient
            $user->notify(new AppointmentRequested($result));

            // 2. Notify Admins
            $admins = User::where('role', UserRole::Admin)->get();
            Notification::send($admins, new NewAppointmentRequest($result));
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
                    $message = 'Appointment cancelled. WARNING: Your account has been RESTRICTED for 30 days due to multiple late cancellations.';
                } else {
                    $message = 'Appointment cancelled. You received a STRIKE for cancelling less than 24 hours in advance.';
                }
            }

            $appointment->update([
                'status' => AppointmentStatus::Cancelled,
                'cancellation_reason' => $request->cancellation_reason
            ]);
            
            // --- NOTIFICATIONS ---
            
            // 1. Notify Patient (Confirmation)
            $user->notify(new AppointmentStatusChanged($appointment));

            // 2. Notify Admins
            $admins = User::where('role', UserRole::Admin)->get();
            Notification::send($admins, new AppointmentCancelled($appointment));

            return back()->with('success', $message);
        }

        return back()->with('error', 'This appointment cannot be cancelled.');
    }

    // --- NEW: Download PDF Method ---
    public function downloadPrescription($id)
    {
        $appointment = Appointment::findOrFail($id);
        $user = Auth::user();

        // Security Check: Only an Admin or the specific Patient who booked it can download
        if ($user->role !== UserRole::Admin && $appointment->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this medical record.');
        }

        // Must be a completed appointment
        if ($appointment->status !== AppointmentStatus::Completed) {
            abort(404, 'Prescription not available because the appointment is not completed.');
        }

        // Load the view and pass the appointment data
        $pdf = Pdf::loadView('pdf.prescription', compact('appointment'));

        // Format filename: clearoptics-rx-105-Smith.pdf
        $lastName = preg_replace('/[^A-Za-z0-9\-]/', '', $appointment->patient_last_name);
        $fileName = "clearoptics-rx-{$appointment->id}-{$lastName}.pdf";

        return $pdf->download($fileName);
    }
}