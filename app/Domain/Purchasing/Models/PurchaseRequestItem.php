<?php

namespace App\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'purchase_request_id', 'item_name', 'qty', 'notes'
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function request()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }
}
