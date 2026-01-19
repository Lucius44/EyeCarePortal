<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $this refers to the Appointment model instance
        return [
            'title' => $this->user->first_name . ' - ' . $this->service,
            // FullCalendar expects 'start' in ISO8601 format (YYYY-MM-DDTHH:mm:ss)
            'start' => $this->appointment_date->format('Y-m-d') . 'T' . $this->appointment_time,
            'color' => $this->status->color(),
            'extendedProps' => [
                'status' => $this->status,
                'description' => $this->description,
            ]
        ];
    }
}