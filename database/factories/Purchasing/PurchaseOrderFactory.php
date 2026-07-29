<?php

namespace Database\Factories\Purchasing;

use App\Domain\Purchasing\Models\PurchaseOrder;
use App\Domain\Purchasing\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(500000, 20000000);
        $tax = $subtotal * 0.11;

        return [
            'number'       => 'PO-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6)),
            'supplier_id'  => Supplier::factory(),
            'date'         => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'eta'          => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'notes'        => $this->faker->sentence(),
            'subtotal'     => $subtotal,
            'tax_amount'   => $tax,
            'total_amount' => $subtotal + $tax,
            'status'       => $this->faker->randomElement(['draft', 'approved', 'partial', 'completed', 'cancelled']),
        ];
    }
}
