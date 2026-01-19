<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get a random service from the list defined in your Model
        $services = Appointment::getServices();

        return [
            'user_id' => User::factory(), // Automatically create a user if one isn't provided
            'service' => fake()->randomElement($services),
            'appointment_date' => fake()->dateTimeBetween('-1 month', '+2 months'),
            'appointment_time' => fake()->randomElement(['09:00', '10:00', '11:00', '13:00', '14:00', '15:00']),
            'description' => fake()->sentence(),
            'status' => fake()->randomElement(AppointmentStatus::cases()),
        ];
    }
}