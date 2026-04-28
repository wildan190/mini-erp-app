<?php

namespace Database\Factories\HRM;

use App\Models\HRM\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->jobTitle() . ' Department',
            'description' => $this->faker->sentence(),
        ];
    }
}
