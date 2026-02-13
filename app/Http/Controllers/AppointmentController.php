<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;
use App\Services\AppointmentService; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
        $userId = Auth::id();

        // 1. Patient-Specific Rule: One active appointment at a time
        $hasActive = Appointment::where('user_id', $userId)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->exists();

        if ($hasActive) {
            return redirect()->back()->withErrors(['error' => 'You already have an active appointment.']);
        }

        // 2. Prepare Data
        $data = $request->validated();
        $data['user_id'] = $userId; // Attach the logged-in user

        // 3. Delegate to Service
        $result = $this->appointmentService->createAppointment($data, 'patient');

        // 4. Handle Result
        if (is_array($result) && isset($result['error'])) {
            return redirect()->back()->withErrors(['error' => $result['error']])->withInput();
        }

        return redirect()->route('appointments.index')->with('success', 'Appointment request submitted successfully!');
    }

    public function cancel(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($appointment->status === AppointmentStatus::Pending) {
            $appointment->delete();
            return back()->with('success', 'Appointment request removed successfully.');
        }

        if ($appointment->status === AppointmentStatus::Confirmed) {
            $request->validate([
                'cancellation_reason' => 'required|string|max:255',
            ]);

            $appointment->update([
                'status' => AppointmentStatus::Cancelled,
                'cancellation_reason' => $request->cancellation_reason
            ]);
            
            return back()->with('success', 'Appointment cancelled successfully.');
        }

        return back()->with('error', 'This appointment cannot be cancelled.');
    }
}