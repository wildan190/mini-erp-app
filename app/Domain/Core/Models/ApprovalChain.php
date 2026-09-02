<?php

namespace App\Domain\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ApprovalChain extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'name',
        'module',
        'model_type',
        'min_amount',
        'max_amount',
        'is_active',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function steps()
    {
        return $this->hasMany(ApprovalStep::class, 'approval_chain_id')->orderBy('step_order');
    }

    public function requests()
    {
        return $this->hasMany(ApprovalRequest::class, 'approval_chain_id');
    }
}
