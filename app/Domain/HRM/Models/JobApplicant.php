<?php

namespace App\Domain\HRM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplicant extends Model
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
        'job_post_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'address',
        'resume_path',
        'portfolio_url',
        'stage',
        'notes',
        'expected_salary',
        'converted_employee_id',
    ];

    protected $casts = [
        'expected_salary' => 'decimal:2',
    ];

    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class);
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }

    public function offeringLetters()
    {
        return $this->hasMany(OfferingLetter::class);
    }

    public function latestOfferingLetter()
    {
        return $this->hasOne(OfferingLetter::class)->latestOfMany();
    }

    public function convertedEmployee()
    {
        return $this->belongsTo(Employee::class, 'converted_employee_id');
    }
}
