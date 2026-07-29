<?php

namespace Database\Factories\HRM;

use App\Domain\HRM\Models\LeaveRequest;
use App\Domain\HRM\Models\Employee;
use App\Domain\HRM\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $endDate = (clone $startDate)->modify('+' . rand(1, 5) . ' days');

        return [
            'employee_id' => Employee::factory(),
            'leave_type_id' => LeaveType::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'reason' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'total_days' => $startDate->diff($endDate)->days + 1,
        ];
    }
}
