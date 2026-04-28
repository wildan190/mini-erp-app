<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'lead_name' => $this->faker->name(),
            'company' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'source' => $this->faker->randomElement(['Website', 'Referral', 'Social Media', 'Advertisement']),
            'status' => $this->faker->randomElement(['new', 'contacted', 'qualified', 'lost']),
            'notes' => $this->faker->paragraph(),
        ];
    }
}
