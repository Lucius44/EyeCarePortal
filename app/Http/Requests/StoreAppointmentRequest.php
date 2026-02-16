<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today',
                function ($attribute, $value, $fail) {
                    $date = Carbon::parse($value);
                    $now = Carbon::now('Asia/Manila');
                    
                    // Check if the selected date is technically "Tomorrow" relative to server time
                    if ($date->copy()->startOfDay()->eq($now->copy()->addDay()->startOfDay())) {
                        // If it is tomorrow, check if it's past 8:00 PM (20:00)
                        if ($now->hour >= 20) {
                            $fail('Bookings for tomorrow close at 8:00 PM. Please choose a later date.');
                        }
                    }
                },
            ],
            'appointment_time' => 'required|date_format:h:i A', 
            'service' => 'required|string',
            'description' => 'nullable|string',

            // --- NEW: Dependent Booking Validation ---
            'is_guest' => 'sometimes|accepted', // The checkbox
            'patient_first_name' => 'required_if:is_guest,on|nullable|string|max:255',
            'patient_last_name' => 'required_if:is_guest,on|nullable|string|max:255',
            'relationship' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_time.date_format' => 'The time must be in the format HH:MM AM/PM (e.g., 09:00 AM).',
            'patient_first_name.required_if' => 'Patient First Name is required when booking for someone else.',
            'patient_last_name.required_if' => 'Patient Last Name is required when booking for someone else.',
        ];
    }
}