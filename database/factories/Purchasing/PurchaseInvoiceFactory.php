<?php

namespace Database\Factories\Purchasing;

use App\Domain\Purchasing\Models\PurchaseInvoice;
use App\Domain\Purchasing\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PurchaseInvoiceFactory extends Factory
{
    protected $model = PurchaseInvoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(1000000, 30000000);
        $date = $this->faker->dateTimeBetween('-1 month', 'now');

        return [
            'number'       => 'INV-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6)),
            'supplier_id'  => Supplier::factory(),
            'date'         => $date->format('Y-m-d'),
            'due_date'     => (clone $date)->modify('+30 days')->format('Y-m-d'),
            'subtotal'     => $subtotal,
            'tax_amount'   => 0,
            'total_amount' => $subtotal,
            'status'       => $this->faker->randomElement(['draft', 'open', 'paid', 'cancelled']),
        ];
    }
}
