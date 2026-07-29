<?php

namespace App\Domain\Project\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProjectTimesheet extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'project_uuid', 'task_uuid', 'employee_uuid', 'date', 
        'hours', 'notes', 'status'
    ];

    protected static function newFactory()
    {
        return \Database\Factories\Project\ProjectTimesheetFactory::new();
    }

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

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_uuid', 'uuid');
    }
}
