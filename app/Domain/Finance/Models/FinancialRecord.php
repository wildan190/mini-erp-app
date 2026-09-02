<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialRecord extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'type',
        'category',
        'amount',
        'status', // pending, approved, rejected
        'approved_by_user_id',
        'approved_by_name',
        'approved_at',
        'rejection_reason',
        'record_date',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'record_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by_user_id');
    }
}
