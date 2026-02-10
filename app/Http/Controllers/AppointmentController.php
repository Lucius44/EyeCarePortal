<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\DaySetting;
use App\Enums\AppointmentStatus;
use App\Services\AppointmentService; // Import the new Service
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected $appointmentService;

    // Inject the Service
    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index()
    {
        $userId = Auth::id();

        // Active Appointment Check
        $activeAppointment = Appointment::where('user_id', $userId)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->first();

        // Delegate heavy lifting to Service
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

        // 1. Check for active
        $hasActive = Appointment::where('user_id', $userId)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->exists();

        if ($hasActive) {
            return redirect()->back()->withErrors(['error' => 'You already have an active appointment.']);
        }

        // 2. Check Settings
        $date = $request->appointment_date;
        $setting = DaySetting::where('date', $date)->first();

        if ($setting && $setting->is_closed) {
            return redirect()->back()->withErrors(['error' => 'The clinic is closed on this date.']);
        }

        $limit = $setting ? $setting->max_appointments : 5;

        $countOnDate = Appointment::where('appointment_date', $date)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->count();

        if ($countOnDate >= $limit) {
            return redirect()->back()->withErrors(['error' => 'This date is fully booked.']);
        }

        // 3. Check Double Booking
        $isTaken = Appointment::where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $request->appointment_time)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->exists();

        if ($isTaken) {
            return redirect()->back()->withErrors(['error' => 'The selected time slot is already taken.']);
        }

        // 4. Create
        Appointment::create([
            'user_id' => $userId,
            'service' => $request->service,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'description' => $request->description,
            'status' => AppointmentStatus::Pending
        ]);

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