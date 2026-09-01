<?php

namespace App\Domain\HRM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfferingLetter extends Model
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
        'offer_number',
        'basic_salary',
        'benefits',
        'joining_date',
        'expiry_date',
        'terms_and_conditions',
        'status',
        'responded_at',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'joining_date' => 'date',
        'expiry_date' => 'date',
        'responded_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(JobApplicant::class, 'job_applicant_id');
    }
}
