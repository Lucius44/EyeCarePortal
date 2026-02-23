<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // --- NEW: Allow user_id to be passed ---
            'user_id' => 'nullable|integer|exists:users,id',
            
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'patient_suffix' => 'nullable|string|max:10',
            
            // Email is nullable for Admins (in case of phone-only booking)
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'service' => 'required|string',
            'description' => 'nullable|string',
            
            // Optional: Relationship if booking for dependent
            'relationship' => 'nullable|string|max:50'
        ];
    }
}