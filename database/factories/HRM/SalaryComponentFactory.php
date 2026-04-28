<?php

namespace Database\Factories\HRM;

use App\Models\HRM\SalaryComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalaryComponentFactory extends Factory
{
    protected $model = SalaryComponent::class;

    public function definition(): array
    {
        $types = ['earning', 'deduction'];
        $isFixed = $this->faker->boolean();
        
        return [
            'name' => $this->faker->unique()->word() . ' Allowance',
            'type' => $this->faker->randomElement($types),
            'is_taxable' => $this->faker->boolean(),
            'is_fixed' => $isFixed,
            'value' => $isFixed ? $this->faker->numberBetween(100000, 1000000) : $this->faker->numberBetween(1, 10),
            'percentage_of' => $isFixed ? null : 'basic_salary',
            'is_active' => true,
        ];
    }
}
