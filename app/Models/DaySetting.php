<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaySetting extends Model
{
    protected $fillable = [
        'date',
        'max_appointments',
        'is_closed',
    ];

    protected $casts = [
        'date' => 'date',
        'is_closed' => 'boolean',
    ];
}