<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'appointment_date' => 'required|date|after_or_equal:today',
            // NEW: Strict validation to match frontend format (e.g. 09:00 AM)
            'appointment_time' => 'required|date_format:h:i A', 
            'service' => 'required|string',
            'description' => 'nullable|string',
        ];
    }

    // Optional: Custom error message to help future developers/API users
    public function messages(): array
    {
        return [
            'appointment_time.date_format' => 'The time must be in the format HH:MM AM/PM (e.g., 09:00 AM).',
        ];
    }
}