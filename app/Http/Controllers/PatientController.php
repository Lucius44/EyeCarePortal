<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class PatientController extends Controller
{
    // 1. The Main Dashboard
    public function dashboard()
    {
        return view('patient.dashboard');
    }

    // 2. The Profile Page
    public function profile()
    {
        $user = Auth::user(); // Get the currently logged-in user
        return view('patient.profile', compact('user'));
    }

    // Show Settings Page
    public function settings()
    {
        return view('patient.settings');
    }

    // Handle ID Upload
    public function uploadId(Request $request)
    {
        $request->validate([
            'id_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
        ]);

        /** @var \App\Models\User $user */ // <--- ADD THIS LINE
        $user = Auth::user();

        // 1. Store the file in the 'public' folder
        // It will be saved in: storage/app/public/id_photos
        $path = $request->file('id_photo')->store('id_photos', 'public');

        // 2. Save the path to the database
        $user->update([
            'id_photo_path' => $path
        ]);

        return back()->with('success', 'ID uploaded successfully! Please wait for Admin approval.');
    }

    // Show Patient's Appointment List
    public function myAppointments()
    {
        $user_id = Auth::id();

        // 1. Upcoming (Pending or Confirmed)
        $upcoming = Appointment::where('user_id', $user_id)
                               ->whereIn('status', ['pending', 'confirmed'])
                               ->orderBy('appointment_date')
                               ->get();

        // 2. History (Completed, Cancelled, No-Show)
        $history = Appointment::where('user_id', $user_id)
                              ->whereIn('status', ['completed', 'cancelled', 'no-show'])
                              ->orderByDesc('appointment_date')
                              ->get();

        return view('patient.my_appointments', compact('upcoming', 'history'));
    }
}