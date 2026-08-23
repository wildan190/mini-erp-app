<?php

namespace App\Domain\Project\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCost extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'project_uuid', 'type', 'description', 'amount', 'date', 'reference_uuid'
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_uuid', 'uuid');
    }
}
