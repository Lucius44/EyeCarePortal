<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
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
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'service' => 'required',
        ]);

        // 2. Create the Appointment
        Appointment::create([
            'user_id' => Auth::id(), // Link to the logged-in user
            'service' => $request->service,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'description' => $request->description,
            'status' => 'pending'
        ]);

        // 3. Redirect back with a success message
        return redirect()->route('dashboard')->with('success', 'Appointment request submitted successfully!');
    }
}