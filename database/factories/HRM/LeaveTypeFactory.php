<?php

namespace Database\Factories\HRM;

use App\Domain\HRM\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Annual Leave', 'Sick Leave', 'Maternity Leave', 'Paternity Leave']),
            'days_per_year' => $this->faker->numberBetween(5, 12),
            'is_paid' => true,
        ];
    }
}
