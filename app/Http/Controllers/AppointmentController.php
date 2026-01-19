<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    // Show the Calendar
    public function index()
    {
        // Get the list of services we defined in the Model
        $services = Appointment::getServices();
        
        // Pass them to the view so we can show them in the dropdown
        return view('patient.appointments', compact('services'));
    }

    // Save the Appointment
    public function store(StoreAppointmentRequest $request)
    {
        // Note: We don't need $request->validate(...) here anymore.
        // If validation fails, Laravel automatically redirects back with errors.

        // Create the Appointment
        Appointment::create([
            'user_id' => Auth::id(),
            'service' => $request->service,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'description' => $request->description,
            'status' => AppointmentStatus::Pending // <--- Fixed: Using Enum here
        ]);

        // Redirect back with a success message
        return redirect()->route('dashboard')->with('success', 'Appointment request submitted successfully!');
    }
}