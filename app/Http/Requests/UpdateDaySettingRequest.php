<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class UpdateDaySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
            // 1. Enforce the Min/Max limits (1 to 9 slots) we discussed
            'max_appointments' => 'required|integer|min:1|max:9',
            'is_closed' => 'required|boolean',
            // 2. Reason is required if closing the day
            'close_reason' => 'nullable|string|required_if:is_closed,1|max:255',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $date = Carbon::parse($this->date)->format('Y-m-d');
            $isClosed = $this->boolean('is_closed');
            $newLimit = $this->integer('max_appointments');

            // --- Query Active Appointments for this Date ---
            $activeAppointments = Appointment::where('appointment_date', $date)
                ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
                ->get();

            $confirmedCount = $activeAppointments->where('status', AppointmentStatus::Confirmed)->count();
            $totalActive = $activeAppointments->count();

            // --- RULE 1: Cannot close a day with CONFIRMED appointments ---
            if ($isClosed && $confirmedCount > 0) {
                $validator->errors()->add(
                    'is_closed', 
                    "Cannot close this date. There are {$confirmedCount} confirmed appointments. Please reschedule them manually first."
                );
            }

            // --- RULE 2: Cannot lower limit below ACTIVE (Pending + Confirmed) count ---
            // If the day is NOT closed, we must ensure the new limit fits the existing bookings.
            if (! $isClosed && $totalActive > $newLimit) {
                $validator->errors()->add(
                    'max_appointments', 
                    "You cannot lower the limit to {$newLimit}. There are currently {$totalActive} active appointments (Pending + Confirmed) for this date."
                );
            }
        });
    }
}