<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserRole;
use App\Enums\UserStatus;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Populate Services
        $this->call(ServiceSeeder::class);

        // 2. Create Default Admin Account
        // Credentials: admin@eyecareportal.com / Password123
        User::create([
            'first_name'     => 'System',
            'middle_name'    => null,
            'last_name'      => 'Admin',
            'suffix'         => null,
            'email'          => 'admin@eyecareportal.com',
            'password'       => 'Password123', // Automatically hashed by User model casts
            'phone_number'   => '09123456789',
            'birthday'       => '1990-01-01',
            'gender'         => 'Male', // Adjust if needed
            'role'           => UserRole::Admin,
            'account_status' => UserStatus::Active,
            'is_verified'    => true,
            'strikes'        => 0,
            'restricted_until' => null,
        ]);
    }
}