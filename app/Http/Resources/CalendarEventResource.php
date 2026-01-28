<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CalendarEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Use the helper we created in the Model
        $patientName = $this->patient_name; 

        $time24 = Carbon::parse($this->appointment_time)->format('H:i:s');

        return [
            'title' => $patientName . ' - ' . $this->service,
            'start' => $this->appointment_date->format('Y-m-d') . 'T' . $time24,
            'color' => $this->status->color(),
            'extendedProps' => [
                'status' => $this->status,
                'description' => $this->description,
            ]
        ];
    }
}