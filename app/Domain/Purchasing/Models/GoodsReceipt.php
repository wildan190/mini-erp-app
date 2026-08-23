<?php

namespace App\Domain\Purchasing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    use HasUuids;

    protected $fillable = [
        'number', 'purchase_order_id', 'date', 'received_by_id', 'notes'
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by_id');
    }
}
