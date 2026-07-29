<?php

namespace Database\Factories\Project;

use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectTimesheet;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectTimesheetFactory extends Factory
{
    protected $model = ProjectTimesheet::class;

    public function definition(): array
    {
        return [
            'project_uuid'  => Project::factory(),
            'employee_uuid' => $this->faker->uuid(),
            'date'          => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'hours'         => $this->faker->randomElement([2, 4, 6, 8]),
            'notes'         => $this->faker->sentence(),
            'status'        => $this->faker->randomElement(['draft', 'submitted', 'approved']),
        ];
    }
}
