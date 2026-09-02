<?php

namespace App\Domain\Project\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTask extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'project_uuid', 'parent_task_uuid', 'name', 'description', 
        'assigned_employee_uuid', 'start_date', 'due_date', 
        'progress_percentage', 'status', 'is_milestone', 'order'
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_uuid', 'uuid');
    }

    public function subtasks()
    {
        return $this->hasMany(ProjectTask::class, 'parent_task_uuid', 'uuid');
    }

    public function assigned_employee()
    {
        return $this->belongsTo(\App\Domain\HRM\Models\Employee::class, 'assigned_employee_uuid', 'uuid');
    }
}
