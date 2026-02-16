<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'appointment_date',
        'appointment_time',
        'service',
        'description',
        'status',
        'cancellation_reason',
        'diagnosis',
        'prescription',
        'patient_first_name',
        'patient_middle_name',
        'patient_last_name',
        'patient_email',
        'patient_phone',
        'relationship', // Ensure this is in your migration or fillable if you added it
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'status' => AppointmentStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // --- Time Mutator ---
    public function setAppointmentTimeAttribute($value)
    {
        $this->attributes['appointment_time'] = Carbon::parse($value)->format('h:i A');
    }

    // --- Helpers to get Patient Info (User OR Guest/Dependent) ---
    public function getPatientNameAttribute()
    {
        // 1. Priority: Specific Patient Fields (Booking for child/other)
        // If these columns are filled, it means the user booked for someone else.
        if (!empty($this->attributes['patient_first_name'])) {
            $name = $this->attributes['patient_first_name'];
            if (!empty($this->attributes['patient_middle_name'])) {
                $name .= ' ' . $this->attributes['patient_middle_name'];
            }
            $name .= ' ' . $this->attributes['patient_last_name'];
            return $name;
        }

        // 2. Fallback: Account Holder (Booking for self)
        if ($this->user) {
            return $this->user->first_name . ' ' . $this->user->last_name;
        }
        
        // 3. Fallback: Legacy/Guest
        return $this->attributes['patient_first_name'] ?? 'Guest Patient';
    }

    public function getPatientEmailAttribute()
    {
        // If specific email provided for dependent, use it (optional)
        if (!empty($this->attributes['patient_email'])) {
            return $this->attributes['patient_email'];
        }

        // Otherwise default to account email
        if ($this->user) {
            return $this->user->email;
        }
        return $this->attributes['patient_email'] ?? null;
    }

    // --- REFACTORED: Fetch Services from DB ---
    public static function getServices()
    {
        // 1. Get all services EXCEPT 'Others', sorted alphabetically
        $services = Service::where('name', '!=', 'Others')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
        
        // 2. Append 'Others' at the very end
        $services[] = 'Others';
        
        return $services;
    }
}