<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;
use App\Enums\UserStatus; // <--- Import the new Enum

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birthday',
        'gender',
        'email',
        'password',
        'phone_number',
        'role',
        'id_photo_path',
        'is_verified',
        'rejection_reason',
        'account_status',
        'strikes',
        'restricted_until'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'birthday' => 'date',
            'restricted_until' => 'datetime',
            'role' => UserRole::class,
            'account_status' => UserStatus::class, // <--- Cast to the new Enum
        ];
    }
}