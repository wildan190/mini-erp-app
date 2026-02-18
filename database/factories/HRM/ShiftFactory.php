<?php

namespace Database\Factories\HRM;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HRM\Shift>
 */
class ShiftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Morning Shift', 'Day Shift', 'Night Shift', 'Evening Shift']),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ];
    }
}
