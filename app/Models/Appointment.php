<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AppointmentStatus;
use Carbon\Carbon; // Added Carbon for time formatting

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'appointment_date',
        'appointment_time',
        'service',
        'description',
        'status',
        'cancellation_reason',
        'patient_first_name',
        'patient_middle_name',
        'patient_last_name',
        'patient_email',
        'patient_phone',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'status' => AppointmentStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // --- NEW: Time Mutator (The Safety Fix) ---
    // This ensures that '9:00 am', '09:00 am', or '9:00 AM' all become '09:00 AM'
    public function setAppointmentTimeAttribute($value)
    {
        $this->attributes['appointment_time'] = Carbon::parse($value)->format('h:i A');
    }

    // --- Helpers to get Patient Info (User OR Guest) ---

    public function getPatientNameAttribute()
    {
        if ($this->user) {
            return $this->user->first_name . ' ' . $this->user->last_name;
        }
        
        // Handle Guest Name with Middle Name support
        $name = $this->patient_first_name;
        if ($this->patient_middle_name) {
            $name .= ' ' . $this->patient_middle_name;
        }
        $name .= ' ' . $this->patient_last_name;
        
        return $name;
    }

    public function getPatientEmailAttribute()
    {
        if ($this->user) {
            return $this->user->email;
        }
        return $this->attributes['patient_email'] ?? null;
    }

    // --- Define Available Services ---
    public static function getServices()
    {
        return [
            'General Checkup',
            'Laser Treatment',
            'Glasses/Contacts Fitting',
        ];
    }
}