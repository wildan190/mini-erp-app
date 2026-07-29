<?php

namespace Database\Factories\Project;

use App\Domain\Project\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-3 months', 'now');

        return [
            'name'        => $this->faker->catchPhrase() . ' System',
            'code'        => 'PROJ-' . strtoupper($this->faker->bothify('??###')),
            'client_name' => $this->faker->company(),
            'description' => $this->faker->paragraph(),
            'start_date'  => $startDate->format('Y-m-d'),
            'end_date'    => (clone $startDate)->modify('+6 months')->format('Y-m-d'),
            'status'      => $this->faker->randomElement(['planning', 'active', 'on_hold', 'completed']),
            'priority'    => $this->faker->randomElement(['low', 'medium', 'high']),
            'value'       => $this->faker->numberBetween(50000000, 500000000),
        ];
    }
}
