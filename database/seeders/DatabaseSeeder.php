<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // We only call the ServiceSeeder to populate the services table.
        // User and Appointment factories have been removed to allow for manual testing.
        $this->call(ServiceSeeder::class);
    }
}