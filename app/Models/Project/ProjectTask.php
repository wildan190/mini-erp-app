<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProjectTask extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'project_uuid', 'parent_task_uuid', 'name', 'description', 
        'assigned_employee_uuid', 'start_date', 'due_date', 
        'progress_percentage', 'status', 'is_milestone', 'order'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
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
        return $this->belongsTo(\App\Models\HRM\Employee::class, 'assigned_employee_uuid', 'uuid');
    }
}
