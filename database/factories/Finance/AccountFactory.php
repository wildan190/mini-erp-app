<?php

namespace Database\Factories\Finance;

use App\Models\Finance\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        $types = ['asset', 'liability', 'equity', 'revenue', 'expense'];
        return [
            'code' => $this->faker->unique()->numerify('####'),
            'name' => $this->faker->word() . ' Account',
            'type' => $this->faker->randomElement($types),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
