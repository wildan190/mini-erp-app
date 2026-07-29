<?php

namespace Database\Factories\Project;

use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectCost;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectCostFactory extends Factory
{
    protected $model = ProjectCost::class;

    public function definition(): array
    {
        return [
            'project_uuid' => Project::factory(),
            'type'         => $this->faker->randomElement(['labor', 'material', 'operational', 'other']),
            'description'  => $this->faker->sentence(),
            'amount'       => $this->faker->numberBetween(100000, 15000000),
            'date'         => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
        ];
    }
}
