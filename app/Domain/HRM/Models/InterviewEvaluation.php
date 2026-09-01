<?php

namespace App\Domain\HRM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterviewEvaluation extends Model
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
        'interview_id',
        'evaluator_name',
        'technical_score',
        'communication_score',
        'culture_fit_score',
        'overall_score',
        'feedback_notes',
        'recommendation',
    ];

    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }
}
