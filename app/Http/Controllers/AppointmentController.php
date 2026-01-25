<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    // Show the Calendar
    public function index()
    {
        $userId = Auth::id();

        // 1. Check if user already has an active appointment
        // CHANGED: We now fetch the *object* (first()) instead of just exists()
        // so we can use its ID and Date in the Cancel Modal.
        $activeAppointment = Appointment::where('user_id', $userId)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->first();

        // 2. Fetch all future appointments to calculate "Counts" and "Taken Slots"
        $appointments = Appointment::where('appointment_date', '>=', Carbon::today())
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->get();

        // 3. Prepare data structures for the frontend
        $dailyCounts = [];
        $takenSlots = [];

        foreach ($appointments as $app) {
            $date = $app->appointment_date->format('Y-m-d');
            
            // Increment Count
            if (!isset($dailyCounts[$date])) {
                $dailyCounts[$date] = 0;
            }
            $dailyCounts[$date]++;

            // Track Taken Time Slots
            if (!isset($takenSlots[$date])) {
                $takenSlots[$date] = [];
            }
            $takenSlots[$date][] = $app->appointment_time;
        }

        $services = Appointment::getServices();
        
        return view('patient.appointments', compact(
            'services', 
            'activeAppointment', // Passing the full object or null
            'dailyCounts', 
            'takenSlots'
        ));
    }

    // Save the Appointment
    public function store(StoreAppointmentRequest $request)
    {
        $userId = Auth::id();

        // RULE 1: One active appointment per user
        $hasActive = Appointment::where('user_id', $userId)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->exists();

        if ($hasActive) {
            return redirect()->back()->withErrors(['error' => 'You already have an active appointment. Please complete or cancel it before booking a new one.']);
        }

        // RULE 2: Max 5 appointments per day
        $countOnDate = Appointment::where('appointment_date', $request->appointment_date)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->count();

        if ($countOnDate >= 5) {
            return redirect()->back()->withErrors(['error' => 'This date is fully booked (Max 5 appointments reached). Please choose another date.']);
        }

        // RULE 3: Prevent Double Booking (Time Slot)
        $isTaken = Appointment::where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $request->appointment_time)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->exists();

        if ($isTaken) {
            return redirect()->back()->withErrors(['error' => 'The selected time slot is already taken. Please choose another time.']);
        }

        // Create the Appointment
        Appointment::create([
            'user_id' => $userId,
            'service' => $request->service,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'description' => $request->description,
            'status' => AppointmentStatus::Pending
        ]);

        return redirect()->route('dashboard')->with('success', 'Appointment request submitted successfully!');
    }

    // NEW: Cancel Appointment
    public function cancel($id)
    {
        $appointment = Appointment::findOrFail($id);

        // Security: Ensure the logged-in user owns this appointment
        if ($appointment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Security: Only allow cancelling if it is Pending or Confirmed
        if (!in_array($appointment->status, [AppointmentStatus::Pending, AppointmentStatus::Confirmed])) {
            return back()->with('error', 'This appointment cannot be cancelled.');
        }

        $appointment->update([
            'status' => AppointmentStatus::Cancelled
        ]);

        return back()->with('success', 'Appointment cancelled successfully.');
    }
}