<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Carbon\Carbon;
// --- IMPORT THE CUSTOM NOTIFICATIONS ---
use App\Notifications\VerifyEmailNotification;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable implements MustVerifyEmail
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
        'restricted_until',
        'email_otp',
        'email_otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_otp',
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
            'account_status' => UserStatus::class,
            'email_otp_expires_at' => 'datetime',
        ];
    }

    /**
     * Generate a new 6-digit Email OTP and set expiration time.
     */
    public function generateEmailOTP()
    {
        $this->email_otp = (string) random_int(100000, 999999);
        $this->email_otp_expires_at = Carbon::now()->addMinutes(15);
        $this->save();

        return $this->email_otp;
    }

    /**
     * Clear the OTP fields after successful verification.
     */
    public function clearEmailOTP()
    {
        $this->email_otp = null;
        $this->email_otp_expires_at = null;
        $this->save();
    }

    /**
     * Send the email verification notification.
     * OVERRIDE: Uses our custom VerifyEmailNotification
     */
    public function sendEmailVerificationNotification()
    {
        // Generate a fresh OTP right before sending the notification
        $this->generateEmailOTP();
        
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Send the password reset notification.
     * OVERRIDE: Uses our custom ResetPasswordNotification
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}