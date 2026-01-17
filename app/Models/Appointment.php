<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    // Helper to get available services
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

    // Relationship: An appointment belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}