<?php

namespace Database\Factories\Finance;

use App\Domain\Finance\Models\FinancialRecord;
use App\Domain\Finance\Models\Account;
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
