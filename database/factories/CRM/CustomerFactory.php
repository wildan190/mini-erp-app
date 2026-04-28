<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'billing_address' => $this->faker->address(),
            'shipping_address' => $this->faker->address(),
            'customer_type' => $this->faker->randomElement(['corporate', 'individual']),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
