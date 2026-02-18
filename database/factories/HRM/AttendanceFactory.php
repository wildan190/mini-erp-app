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
        return [
            'employee_id' => \App\Models\HRM\Employee::factory(),
            'shift_id' => \App\Models\HRM\Shift::factory(),
            'date' => \Carbon\Carbon::today(),
            'clock_in' => \Carbon\Carbon::now()->subHours(8),
            'clock_out' => null,
            'status' => 'present',
            'face_verification_status' => 'skipped',
            'location_verification_status' => 'skipped',
        ];
    }
}
