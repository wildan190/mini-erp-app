<?php

namespace App\Domain\Project\Contracts;

use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectTask;

interface ProjectServiceInterface
{
    public function getProjects(array $filters = [], int $perPage = 15);

    public function createProject(array $data): Project;

    public function createTask(Project $project, array $data): ProjectTask;
}
