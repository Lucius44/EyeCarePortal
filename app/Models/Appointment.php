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
        'service',
        'appointment_date',
        'appointment_time',
        'description',
        'status',
        'cancellation_reason', // <--- ADD THIS
    ];

    public static function getServices()
    {
        return [
            'General Eye Exam',
            'Contact Lens Fitting',
            'Pediatric Eye Exam',
            'Glaucoma Screening',
            'Cataract Evaluation',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array 
    {
        return [
            'appointment_date' => 'date',
            'status' => AppointmentStatus::class,
        ];
    }
}