<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AppointmentStatus;

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
        // New Fields
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

    // --- Helpers to get Patient Info (User OR Guest) ---

    public function getPatientNameAttribute()
    {
        if ($this->user) {
            return $this->user->first_name . ' ' . $this->user->last_name;
        }
        return $this->patient_first_name . ' ' . $this->patient_last_name;
    }

    public function getPatientEmailAttribute()
    {
        return $this->user ? $this->user->email : $this->patient_email;
    }
}