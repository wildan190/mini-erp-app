<?php

namespace App\Domain\Project\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code', 'name', 'client_name', 'pm_uuid', 'start_date', 
        'end_date', 'status', 'priority', 'value', 'description'
    ];

    protected static function newFactory()
    {
        return \Database\Factories\Project\ProjectFactory::new();
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->code)) {
                $model->code = 'PRJ-' . strtoupper(Str::random(6));
            }
        });
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'project_uuid', 'uuid');
    }

    public function members()
    {
        return $this->hasMany(ProjectMember::class, 'project_uuid', 'uuid');
    }

    public function timesheets()
    {
        return $this->hasMany(ProjectTimesheet::class, 'project_uuid', 'uuid');
    }

    public function costs()
    {
        return $this->hasMany(ProjectCost::class, 'project_uuid', 'uuid');
    }
}
