<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Http\Resources\CalendarEventResource; // <--- Import the new Resource
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        // 1. Get appointments with user data
        $appointments = Appointment::with('user')->get();

        // 2. Transform them using our new Resource
        // ->resolve() converts the resource object into a plain PHP array
        $events = CalendarEventResource::collection($appointments)->resolve();

        // 3. Pass the formatted 'events' to the view
        return view('admin.dashboard', compact('events'));
    }

    // Show the Management Table
    public function appointments()
    {
        // Using Enums in queries
        $pending = Appointment::where('status', AppointmentStatus::Pending)
                              ->with('user')
                              ->orderBy('appointment_date')
                              ->get();

        $confirmed = Appointment::where('status', AppointmentStatus::Confirmed)
                                ->with('user')
                                ->orderBy('appointment_date')
                                ->get();
                                
        // History includes completed, cancelled, and no-show
        $history = Appointment::whereIn('status', [
                                    AppointmentStatus::Completed, 
                                    AppointmentStatus::Cancelled, 
                                    AppointmentStatus::NoShow
                                ])
                              ->with('user')
                              ->orderByDesc('appointment_date')
                              ->get();

        return view('admin.appointments', compact('pending', 'confirmed', 'history'));
    }

    // Handle Status Changes (Accept, Reject, Complete, Cancel)
    public function updateStatus(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        // Validate that the status is a valid one using the Enum Rule
        $request->validate([
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
        ]);

        $appointment->update(['status' => $request->status]);

        return back()->with('success', 'Appointment status updated successfully.');
    }

    // Show User List
    public function users()
    {
        // Users who have uploaded an ID but are NOT verified yet
        $pendingUsers = User::where('role', UserRole::Patient)
                            ->whereNotNull('id_photo_path')
                            ->where('is_verified', false)
                            ->get();

        // All other users (for reference)
        $allUsers = User::where('role', UserRole::Patient)->get();

        return view('admin.users', compact('pendingUsers', 'allUsers'));
    }

    // Approve or Reject User
    public function verifyUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $action = $request->input('action'); // 'approve' or 'reject'

        if ($action === 'approve') {
            $user->update(['is_verified' => true]);
            return back()->with('success', 'User verified successfully!');
        } 
        
        if ($action === 'reject') {
            // If rejected, we might want to delete the photo so they can upload a new one
            // optional: Storage::delete('public/' . $user->id_photo_path);
            
            $user->update([
                'id_photo_path' => null, // Reset so they can try again
                'is_verified' => false
            ]);
            return back()->with('success', 'User verification rejected. They can upload a new ID.');
        }

        return back()->with('error', 'Invalid action.');
    }
}