<?php

namespace Database\Factories\HRM;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HRM\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-1 month', 'now');
        $clockIn = \Carbon\Carbon::instance($date)->setTime(8, rand(0, 30));
        $clockOut = (clone $clockIn)->addHours(rand(8, 10));

        return [
            'employee_id' => \App\Models\HRM\Employee::factory(),
            'shift_id' => \App\Models\HRM\Shift::inRandomOrder()->first()?->id ?? \App\Models\HRM\Shift::factory(),
            'date' => $clockIn->toDateString(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => $clockIn->format('H:i') > '08:15' ? 'late' : 'present',
            'face_verification_status' => 'skipped',
            'location_verification_status' => 'skipped',
        ];
    }
}
