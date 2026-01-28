<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon; // <--- Import Carbon

class CalendarEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // 1. Parse the stored time (e.g., "09:00 AM") to 24-hour format (e.g., "09:00:00")
        $time24 = Carbon::parse($this->appointment_time)->format('H:i:s');

        return [
            // Title: Patient Name - Service
            'title' => $this->user->first_name . ' - ' . $this->service,
            
            // Start: Combine Date + T + 24-Hour Time (Required for FullCalendar)
            // Example Result: "2026-01-28T09:00:00"
            'start' => $this->appointment_date->format('Y-m-d') . 'T' . $time24,
            
            'color' => $this->status->color(),
            
            'extendedProps' => [
                'status' => $this->status,
                'description' => $this->description,
            ]
        ];
    }
}