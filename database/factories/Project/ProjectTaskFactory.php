<?php

namespace Database\Factories\Project;

use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectTaskFactory extends Factory
{
    protected $model = ProjectTask::class;

    public function definition(): array
    {
        return [
            'project_uuid'        => Project::factory(),
            'name'                => $this->faker->bs(),
            'description'         => $this->faker->sentence(),
            'start_date'          => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'due_date'            => $this->faker->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            'progress_percentage' => $this->faker->numberBetween(0, 100),
            'status'              => $this->faker->randomElement(['todo', 'in_progress', 'review', 'done']),
            'is_milestone'        => $this->faker->boolean(20),
            'order'               => $this->faker->numberBetween(1, 20),
        ];
    }
}
