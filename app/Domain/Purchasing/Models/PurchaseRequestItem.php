<?php

namespace App\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'purchase_request_id', 'item_name', 'qty', 'estimated_price', 'notes'
    ];

    protected $casts = [
        'qty'             => 'float',
        'estimated_price' => 'float',
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
