<?php

namespace App\Domain\System\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\User;

class ApprovalHistory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'approval_request_id',
        'step_order',
        'approver_id',
        'action',
        'comments',
    ];

    protected $casts = [
        'step_order' => 'integer',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function request()
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
