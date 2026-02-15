<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            'General Checkup',
            'Prescription Eye Glasses',
            'Glasses/ Contact Lens Fitting',
            'Pediatric Eye Care',
            'Vision Therapy',
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(['name' => $service]);
        }
    }
}