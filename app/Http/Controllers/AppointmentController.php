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
    public function index()
    {
        $userId = Auth::id();

        $activeAppointment = Appointment::where('user_id', $userId)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->first();

        $appointments = Appointment::where('appointment_date', '>=', Carbon::today())
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->get();

        $dailyCounts = [];
        $takenSlots = [];

        foreach ($appointments as $app) {
            $date = $app->appointment_date->format('Y-m-d');
            
            if (!isset($dailyCounts[$date])) {
                $dailyCounts[$date] = 0;
            }
            $dailyCounts[$date]++;

            if (!isset($takenSlots[$date])) {
                $takenSlots[$date] = [];
            }
            $takenSlots[$date][] = $app->appointment_time;
        }

        $services = Appointment::getServices();
        
        return view('patient.appointments', compact(
            'services', 
            'activeAppointment', 
            'dailyCounts', 
            'takenSlots'
        ));
    }

    public function store(StoreAppointmentRequest $request)
    {
        $userId = Auth::id();

        $hasActive = Appointment::where('user_id', $userId)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->exists();

        if ($hasActive) {
            return redirect()->back()->withErrors(['error' => 'You already have an active appointment.']);
        }

        $countOnDate = Appointment::where('appointment_date', $request->appointment_date)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->count();

        if ($countOnDate >= 5) {
            return redirect()->back()->withErrors(['error' => 'This date is fully booked.']);
        }

        $isTaken = Appointment::where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $request->appointment_time)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->exists();

        if ($isTaken) {
            return redirect()->back()->withErrors(['error' => 'The selected time slot is already taken.']);
        }

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

    // UPDATED: Cancel now accepts Request for the reason
    public function cancel(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // 1. Pending -> Hard Delete (Simple Modal, no reason needed)
        if ($appointment->status === AppointmentStatus::Pending) {
            $appointment->delete();
            return back()->with('success', 'Appointment request removed successfully.');
        }

        // 2. Confirmed -> Soft Cancel (Requires Reason)
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