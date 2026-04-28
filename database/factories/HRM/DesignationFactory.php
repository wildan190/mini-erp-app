<?php

namespace Database\Factories\HRM;

use App\Models\HRM\Designation;
use Illuminate\Database\Eloquent\Factories\Factory;

class DesignationFactory extends Factory
{
    protected $model = Designation::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->jobTitle(),
            'description' => $this->faker->sentence(),
        ];
    }
}
