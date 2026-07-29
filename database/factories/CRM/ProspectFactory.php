<?php

namespace Database\Factories\CRM;

use App\Domain\CRM\Models\Prospect;
use App\Domain\CRM\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProspectFactory extends Factory
{
    protected $model = Prospect::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'title' => $this->faker->sentence(3),
            'expected_value' => $this->faker->numberBetween(1000000, 100000000),
            'probability' => $this->faker->numberBetween(10, 90),
            'expected_closing_date' => $this->faker->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['open', 'negotiation', 'won', 'lost']),
        ];
    }
}
