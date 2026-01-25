<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected'; // <--- NEW
    case Completed = 'completed';
    case NoShow = 'no-show';
    
    public function color(): string
    {
        return match($this) {
            self::Confirmed => '#198754', // Green
            self::Pending => '#ffc107',   // Yellow
            self::Cancelled, self::Rejected, self::NoShow => '#dc3545', // Red
            self::Completed => '#0d6efd', // Blue
        };
    }
}