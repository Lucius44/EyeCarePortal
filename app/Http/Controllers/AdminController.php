<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Http\Resources\CalendarEventResource;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Filter out rejected/cancelled from the calendar to keep it clean? 
        // Or keep them? Let's keep them but maybe the frontend filters them.
        // For now, fetching all non-cancelled/rejected makes sense for a "Schedule", 
        // but seeing history is good too. Let's grab all.
        $appointments = Appointment::with('user')->get();
        $events = CalendarEventResource::collection($appointments)->resolve();
        return view('admin.dashboard', compact('events'));
    }

    public function appointments()
    {
        $pending = Appointment::where('status', AppointmentStatus::Pending)
                              ->with('user')
                              ->orderBy('appointment_date')
                              ->get();

        $confirmed = Appointment::where('status', AppointmentStatus::Confirmed)
                                ->with('user')
                                ->orderBy('appointment_date')
                                ->get();
                                
        // History now includes Rejected
        $history = Appointment::whereIn('status', [
                                    AppointmentStatus::Completed, 
                                    AppointmentStatus::Cancelled, 
                                    AppointmentStatus::Rejected, // <--- NEW
                                    AppointmentStatus::NoShow
                                ])
                              ->with('user')
                              ->orderByDesc('appointment_date')
                              ->get();

        return view('admin.appointments', compact('pending', 'confirmed', 'history'));
    }

    public function updateStatus(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        $request->validate([
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
            // If status is rejected, reason is required
            'cancellation_reason' => 'nullable|string|max:500', 
        ]);

        $data = ['status' => $request->status];

        // If rejecting, save the reason
        if ($request->status === AppointmentStatus::Rejected->value) {
            $data['cancellation_reason'] = $request->input('cancellation_reason', 'No reason provided.');
        }

        $appointment->update($data);

        return back()->with('success', 'Appointment status updated successfully.');
    }

    public function users()
    {
        $pendingUsers = User::where('role', UserRole::Patient)
                            ->whereNotNull('id_photo_path')
                            ->where('is_verified', false)
                            ->get();

        $allUsers = User::where('role', UserRole::Patient)->get();

        return view('admin.users', compact('pendingUsers', 'allUsers'));
    }

    public function verifyUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $action = $request->input('action'); 

        if ($action === 'approve') {
            $user->update(['is_verified' => true]);
            return back()->with('success', 'User verified successfully!');
        } 
        
        if ($action === 'reject') {
            $user->update([
                'id_photo_path' => null, 
                'is_verified' => false
            ]);
            return back()->with('success', 'User verification rejected. They can upload a new ID.');
        }

        return back()->with('error', 'Invalid action.');
    }
}