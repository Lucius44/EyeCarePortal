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
            'Prescription Eyeglasses',
            'Contact Lens Fitting',
            'Pediatric Eye Exam',
            'Dry Eye Treatment',
            'Others', 
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(['name' => $service]);
        }
    }
}