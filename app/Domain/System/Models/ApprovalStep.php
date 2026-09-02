<?php

namespace App\Domain\System\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ApprovalStep extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'approval_chain_id',
        'step_order',
        'approver_type',
        'approver_uuid',
        'is_final_step',
    ];

    protected $casts = [
        'is_final_step' => 'boolean',
        'step_order' => 'integer',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function chain()
    {
        return $this->belongsTo(ApprovalChain::class, 'approval_chain_id');
    }
}
