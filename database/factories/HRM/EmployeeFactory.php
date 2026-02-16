<?php

namespace Database\Factories\HRM;

use App\Models\HRM\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'emp_code' => 'EMP-' . $this->faker->unique()->numerify('#####'),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'status' => 'active',
            'joining_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'basic_salary' => $this->faker->numberBetween(3000000, 10000000),
        ];
    }
}
