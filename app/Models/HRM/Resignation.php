<?php

namespace App\Models\HRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resignation extends Model
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
        'employee_id',
        'notice_date',
        'resignation_date',
        'reason',
        'status',
        'handover_to',
        'remarks',
    ];

    protected $casts = [
        'notice_date' => 'date',
        'resignation_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function handoverTo()
    {
        return $this->belongsTo(Employee::class, 'handover_to');
    }
}
