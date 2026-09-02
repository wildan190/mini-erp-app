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
        'project_uuid',
        'type',
        'item_name',
        'quantity',
        'unit_price',
        'amount',
        'status', // pending, approved, rejected
        'approved_by_user_id',
        'approved_by_name',
        'approved_at',
        'rejection_reason',
        'purpose',
        'description',
        'requested_by_employee_uuid',
        'requested_by_name',
        'receipt_attachment_path',
        'date',
        'reference_uuid',
        'finance_record_uuid',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_uuid', 'uuid');
    }

    public function requestedByEmployee()
    {
        return $this->belongsTo(\App\Domain\HRM\Models\Employee::class, 'requested_by_employee_uuid', 'uuid');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by_user_id');
    }
}
