<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Appointment;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin User (Only if they don't exist)
        if (!User::where('email', 'admin@gmail.com')->exists()) {
            User::factory()->create([
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('Password123'),
                'role' => UserRole::Admin,
                'is_verified' => true,
            ]);
        }

        // 2. Create 10 Patients, each with 3 appointments
        User::factory(10)
            ->create()
            ->each(function ($user) {
                Appointment::factory(3)->create([
                    'user_id' => $user->id
                ]);
            });
    }
}