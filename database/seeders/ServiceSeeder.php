<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            'Comprehensive Eye Exam',
            'Pediatric Eye Exam',
            'Contact Lens Fitting',
            'Dry Eye Treatment',
            'Glaucoma Screening',
            'Diabetic Eye Care',
            'Laser Eye Surgery Consultation',
            'Emergency Eye Care',
            'Other', // Added at the end
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(['name' => $service]);
        }
    }
}