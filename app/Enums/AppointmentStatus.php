<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case NoShow = 'no-show';
    
    // Helper to get color for the dashboard
    public function color(): string
    {
        return match($this) {
            self::Confirmed => '#198754', // Green
            self::Pending => '#ffc107',   // Yellow
            self::Cancelled, self::NoShow => '#dc3545', // Red
            self::Completed => '#0d6efd', // Blue
        };
    }
}