<?php

namespace Database\Factories\Purchasing;

use App\Domain\Purchasing\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name'          => $this->faker->company(),
            'pic'           => $this->faker->name(),
            'contact'       => $this->faker->phoneNumber(),
            'address'       => $this->faker->address(),
            'npwp'          => $this->faker->numerify('##.###.###.#-###.000'),
            'category'      => $this->faker->randomElement(['Raw Materials', 'IT Hardware', 'Office Supplies', 'Logistics', 'Services']),
            'currency_code' => 'IDR',
            'is_active'     => true,
        ];
    }
}
