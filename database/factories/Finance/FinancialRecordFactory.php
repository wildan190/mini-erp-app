<?php

namespace Database\Factories\Finance;

use App\Models\Finance\FinancialRecord;
use App\Models\Finance\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinancialRecordFactory extends Factory
{
    protected $model = FinancialRecord::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['revenue', 'expense']),
            'category' => $this->faker->randomElement(['Sales', 'Consultation', 'Rent', 'Utilities', 'Salary', 'Marketing']),
            'amount' => $this->faker->randomFloat(2, 100000, 10000000),
            'record_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'description' => $this->faker->sentence(),
        ];
    }
}
