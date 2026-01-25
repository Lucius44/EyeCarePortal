<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Appointment;
use App\Enums\AppointmentStatus; 

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
        $user = Auth::user(); 
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
            'id_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048', 
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $path = $request->file('id_photo')->store('id_photos', 'public');

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
                               ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
                               ->orderBy('appointment_date')
                               ->get();

        // 2. History (Completed, Cancelled, No-Show, REJECTED)
        // --- FIXED: Added Rejected to the list ---
        $history = Appointment::where('user_id', $user_id)
                              ->whereIn('status', [
                                  AppointmentStatus::Completed, 
                                  AppointmentStatus::Cancelled, 
                                  AppointmentStatus::NoShow,
                                  AppointmentStatus::Rejected // <--- ADDED THIS
                              ])
                              ->orderByDesc('appointment_date')
                              ->get();

        return view('patient.my_appointments', compact('upcoming', 'history'));
    }

    // Update Phone Number
    public function updatePhone(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:20',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update(['phone_number' => $request->phone_number]);

        return back()->with('success', 'Phone number updated successfully.');
    }

    // Change Password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8|regex:/[A-Z]/|regex:/[0-9]/',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}