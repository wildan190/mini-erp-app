<?php

namespace App\Domain\HRM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interview extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected $fillable = [
        'uuid',
        'job_applicant_id',
        'title',
        'scheduled_at',
        'type',
        'meeting_link_or_location',
        'interviewer_name',
        'interviewer_email',
        'instructions',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(JobApplicant::class, 'job_applicant_id');
    }

    public function evaluations()
    {
        return $this->hasMany(InterviewEvaluation::class);
    }
}
