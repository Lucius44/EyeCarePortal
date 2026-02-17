<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Appointment;
use App\Enums\AppointmentStatus; 

class PatientController extends Controller
{
    // 1. The Main Dashboard (With Status Logic)
    public function dashboard()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Fetch Active Appointment for the Dashboard Card
        $activeAppointment = Appointment::where('user_id', $user->id)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->first();

        // Fetch Stats
        $completedVisits = Appointment::where('user_id', $user->id)
            ->where('status', AppointmentStatus::Completed)
            ->count();

        return view('patient.dashboard', compact('activeAppointment', 'completedVisits'));
    }

    // 2. The Profile Page
    public function profile()
    {
        $user = Auth::user(); 
        return view('patient.profile', compact('user'));
    }

    // 4. Show Settings Page
    public function settings()
    {
        return view('patient.settings');
    }

    // --- NEW: Update Personal Info (For Unverified Users) ---
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security Gate: Verified users cannot change core identity details
        if ($user->is_verified) {
            return back()->with('error', 'Your identity is verified. You cannot change your details.');
        }

        $validated = $request->validate([
            // Regex allows letters, spaces, dots, and dashes. NO NUMBERS.
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
            'middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
            // Enforce 18+ years old
            'birthday' => ['required', 'date', 'before:-18 years'],
            'gender' => 'required|string|in:Male,Female,Other',
        ], [
            'first_name.regex' => 'First name cannot contain numbers or special characters.',
            'last_name.regex' => 'Last name cannot contain numbers or special characters.',
            'middle_name.regex' => 'Middle name cannot contain numbers or special characters.',
            'birthday.before' => 'You must be at least 18 years old to register.',
        ]);

        $user->update($validated);

        return back()->with('success', 'Personal information updated successfully.');
    }

    // 5. Handle ID Upload
    public function uploadId(Request $request)
    {
        $request->validate([
            'id_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048', 
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $path = $request->file('id_photo')->store('id_photos', 'public');

        // FIX: Clear the rejection reason so it re-appears in Admin Pending List
        $user->update([
            'id_photo_path' => $path,
            'rejection_reason' => null, 
            'is_verified' => false 
        ]);

        return back()->with('success', 'ID uploaded successfully! Please wait for Admin approval.');
    }

    // 6. Show Patient's Appointment List
    public function myAppointments()
    {
        $user_id = Auth::id();

        // Upcoming remains a collection (get) as requested
        $upcoming = Appointment::where('user_id', $user_id)
                               ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
                               ->orderBy('appointment_date')
                               ->get();

        // History is now paginated (15 per page)
        $history = Appointment::where('user_id', $user_id)
                              ->whereIn('status', [
                                  AppointmentStatus::Completed, 
                                  AppointmentStatus::Cancelled, 
                                  AppointmentStatus::NoShow,
                                  AppointmentStatus::Rejected
                              ])
                              ->orderByDesc('appointment_date')
                              ->paginate(15);

        return view('patient.my_appointments', compact('upcoming', 'history'));
    }

    // 7. Update Phone Number
    public function updatePhone(Request $request)
    {
        $request->validate([
            'phone_number' => ['required', 'string', 'regex:/^09\d{9}$/'],
        ], [
            'phone_number.regex' => 'Please enter a valid Philippine mobile number (e.g., 09123456789).'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update(['phone_number' => $request->phone_number]);

        return back()->with('success', 'Phone number updated successfully.');
    }

    // 8. Change Password
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