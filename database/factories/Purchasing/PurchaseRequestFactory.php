<?php

namespace Database\Factories\Purchasing;

use App\Domain\Purchasing\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PurchaseRequestFactory extends Factory
{
    protected $model = PurchaseRequest::class;

    public function definition(): array
    {
        return [
            'number'       => 'PR-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6)),
            'date'         => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'requestor_id' => User::first()?->id ?? 1,
            'notes'        => $this->faker->sentence(),
            'status'       => $this->faker->randomElement(['draft', 'pending', 'approved', 'rejected']),
        ];
    }
}
