<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\AppointmentStatus;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        // Guest Fields
        'patient_first_name',
        'patient_middle_name',
        'patient_last_name',
        'patient_suffix', // <--- Added
        'patient_email',
        'patient_phone',
        'relationship',
        
        'appointment_date',
        'appointment_time',
        'service',
        'status',
        'description',
        'cancellation_reason',
        
        // Medical Fields
        'diagnosis',
        'prescription',
        'notes'
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'status' => AppointmentStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // --- GLOBAL NAME DISPLAY ACCESSOR ---
    // This automatically fixes the name display in History, Appointments, and Patient tables
    public function getPatientNameAttribute()
    {
        // 1. Registered User or Dependent Booking by User
        if ($this->user) {
            // Check if it's a "Dependent" booking (fields manually filled)
            if ($this->patient_first_name) {
                return $this->formatName(
                    $this->patient_first_name,
                    $this->patient_middle_name,
                    $this->patient_last_name,
                    $this->patient_suffix
                );
            }
            // Standard User Booking
            return $this->formatName(
                $this->user->first_name,
                $this->user->middle_name,
                $this->user->last_name,
                $this->user->suffix
            );
        }

        // 2. Guest/Walk-in
        return $this->formatName(
            $this->patient_first_name,
            $this->patient_middle_name,
            $this->patient_last_name,
            $this->patient_suffix
        );
    }

    // Helper to format name cleanly: "First M. Last Suffix"
    private function formatName($first, $middle, $last, $suffix)
    {
        $name = $first;
        
        if ($middle) {
            $name .= ' ' . $middle;
        }
        
        $name .= ' ' . $last;
        
        if ($suffix) {
            $name .= ' ' . $suffix;
        }

        return $name;
    }
}