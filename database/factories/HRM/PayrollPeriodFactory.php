<?php

namespace Database\Factories\HRM;

use App\Domain\HRM\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PayrollPeriodFactory extends Factory
{
    protected $model = PayrollPeriod::class;

    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-6 months', '+1 month');
        $startOfMonth = Carbon::instance($date)->startOfMonth();
        $endOfMonth = Carbon::instance($date)->endOfMonth();

        return [
            'name' => $startOfMonth->format('F Y'),
            'start_date' => $startOfMonth->toDateString(),
            'end_date' => $endOfMonth->toDateString(),
            'status' => 'draft',
        ];
    }
}
