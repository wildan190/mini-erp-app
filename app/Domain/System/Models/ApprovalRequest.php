<?php

namespace App\Domain\System\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\User;

class ApprovalRequest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'approval_chain_id',
        'approvable_type',
        'approvable_uuid',
        'requester_id',
        'current_step_order',
        'status',
        'notes',
    ];

    protected $casts = [
        'current_step_order' => 'integer',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function chain()
    {
        return $this->belongsTo(ApprovalChain::class, 'approval_chain_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approvable()
    {
        return $this->morphTo(__FUNCTION__, 'approvable_type', 'approvable_uuid', 'uuid');
    }

    public function histories()
    {
        return $this->hasMany(ApprovalHistory::class, 'approval_request_id')->orderBy('created_at', 'asc');
    }
}
