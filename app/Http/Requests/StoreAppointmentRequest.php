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
        // Return true because we already check authentication in the middleware/routes
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
            // Strict validation to match frontend format (e.g. 09:00 AM)
            'appointment_time' => 'required|date_format:h:i A', 
            'service' => 'required|string',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_time.date_format' => 'The time must be in the format HH:MM AM/PM (e.g., 09:00 AM).',
        ];
    }
}