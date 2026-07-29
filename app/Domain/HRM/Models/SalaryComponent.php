<?php

namespace App\Domain\HRM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryComponent extends Model
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
        'name',
        'type',
        'is_taxable',
        'is_fixed',
        'value',
        'percentage_of',
        'is_active',
    ];

    protected $casts = [
        'is_taxable' => 'boolean',
        'is_fixed' => 'boolean',
        'is_active' => 'boolean',
        'value' => 'decimal:2',
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_salary_components')
                    ->withPivot('custom_value')
                    ->withTimestamps();
    }
}
