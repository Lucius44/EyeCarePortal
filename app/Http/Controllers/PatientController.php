<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Models\Appointment;
use App\Models\User;
use App\Enums\AppointmentStatus; 
use App\Enums\UserRole;
// Notifications
use App\Notifications\Admin\NewIdUploaded;

class PatientController extends Controller
{
    public function dashboard()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $activeAppointment = Appointment::where('user_id', $user->id)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->first();

        $completedVisits = Appointment::where('user_id', $user->id)
            ->where('status', AppointmentStatus::Completed)
            ->count();

        return view('patient.dashboard', compact('activeAppointment', 'completedVisits'));
    }

    public function profile()
    {
        $user = Auth::user(); 
        return view('patient.profile', compact('user'));
    }

    public function settings()
    {
        return view('patient.settings');
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->is_verified) {
            return back()->with('error', 'Your identity is verified. You cannot change your details.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
            'middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
            'suffix' => ['nullable', 'string', 'max:10'], 
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

    public function myAppointments()
    {
        $user_id = Auth::id();

        $upcoming = Appointment::where('user_id', $user_id)
                               ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
                               ->orderBy('appointment_date')
                               ->get();

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

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'], 
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $activeAppointments = Appointment::where('user_id', $user->id)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
            ->exists();

        if ($activeAppointments) {
            return back()->with('error', 'You cannot delete your account while you have active appointments. Please cancel them first.');
        }

        if ($user->id_photo_path && Storage::disk('local')->exists($user->id_photo_path)) {
            Storage::disk('local')->delete($user->id_photo_path);
        }

        Auth::logout();
        
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been successfully deleted.');
    }

    public function uploadId(Request $request)
    {
        $request->validate([
            'id_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'data_privacy_consent' => 'required|accepted', 
        ], [
            'data_privacy_consent.required' => 'You must consent to the privacy policy to upload your ID.',
            'data_privacy_consent.accepted' => 'You must consent to the privacy policy to upload your ID.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // --- NEW: Failsafe block for already verified users ---
        if ($user->is_verified) {
            return back()->with('error', 'Your account is already verified. No further uploads are required.');
        }

        if ($user->id_photo_path && Storage::disk('local')->exists($user->id_photo_path)) {
            Storage::disk('local')->delete($user->id_photo_path);
        }

        $path = $request->file('id_photo')->store('id_photos', 'local');

        $user->update([
            'id_photo_path' => $path,
            'rejection_reason' => null, 
            'is_verified' => false 
        ]);

        // --- NEW: Notify Admins of ID Upload ---
        $admins = User::where('role', UserRole::Admin)->get();
        Notification::send($admins, new NewIdUploaded($user));

        return back()->with('success', 'ID uploaded successfully! Please wait for Admin approval.');
    }

    public function showIdPhoto()
    {
        $user = Auth::user();

        if (!$user->id_photo_path) {
            abort(404, 'No ID photo found.');
        }

        if (!Storage::disk('local')->exists($user->id_photo_path)) {
            abort(404, 'File not found in secure storage.');
        }

        $path = Storage::disk('local')->path($user->id_photo_path);

        return response()->file($path);
    }

    // --- NOTIFICATION METHODS ---

    public function markNotificationAsRead($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $notification = $user->notifications()->findOrFail($id);
        
        $notification->markAsRead();

        // Redirect to the URL attached to the notification
        return redirect($notification->data['url'] ?? route('dashboard'));
    }

    public function markAllNotificationsAsRead()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $user->unreadNotifications->markAsRead();
        
        return back();
    }
}