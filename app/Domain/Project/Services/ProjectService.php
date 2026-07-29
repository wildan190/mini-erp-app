<?php

namespace App\Domain\Project\Services;

use App\Domain\Project\Contracts\ProjectServiceInterface;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectTask;

class ProjectService implements ProjectServiceInterface
{
    public function getProjects(array $filters = [], int $perPage = 15)
    {
        $query = Project::query();
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        return $query->paginate($perPage);
    }

    public function createProject(array $data): Project
    {
        return Project::create($data);
    }

    public function createTask(Project $project, array $data): ProjectTask
    {
        $data['project_id'] = $project->id;
        return ProjectTask::create($data);
    }
}
