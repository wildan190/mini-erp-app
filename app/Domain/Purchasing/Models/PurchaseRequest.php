<?php

namespace App\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name', 'number', 'date', 'requestor_id', 'department_uuid', 'notes', 'status'
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function requestor()
    {
        return $this->belongsTo(\App\Models\User::class, 'requestor_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }
}
