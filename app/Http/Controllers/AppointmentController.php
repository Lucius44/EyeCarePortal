<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\DaySetting;
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

        // Fetch taken slots for future checking
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

        // Fetch Settings
        $daySettings = DaySetting::where('date', '>=', Carbon::today())->get()->keyBy(function($item) {
            return $item->date->format('Y-m-d');
        });

        // --- NEW: Pre-calculate Status for the Calendar (Patient Side UX) ---
        $calendarStatus = [];
        // We look ahead 60 days or just use the data we have. 
        // Let's iterate through the dailyCounts keys and DaySettings keys to build a map.
        
        $allDates = array_unique(array_merge(array_keys($dailyCounts), $daySettings->keys()->toArray()));

        foreach ($allDates as $date) {
            $setting = $daySettings[$date] ?? null;
            $count = $dailyCounts[$date] ?? 0;
            
            $limit = $setting ? $setting->max_appointments : 5; // Default 5
            $isClosed = $setting ? $setting->is_closed : false;

            if ($isClosed) {
                $calendarStatus[$date] = 'closed';
            } elseif ($count >= $limit) {
                $calendarStatus[$date] = 'full';
            } else {
                $calendarStatus[$date] = 'open';
            }
        }

        $services = Appointment::getServices();
        
        return view('patient.appointments', compact(
            'services', 
            'activeAppointment', 
            'dailyCounts', 
            'takenSlots',
            'daySettings',
            'calendarStatus' // <--- Passing the calculated status
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