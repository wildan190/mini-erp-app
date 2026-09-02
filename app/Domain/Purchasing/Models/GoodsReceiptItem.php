<?php

namespace App\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'goods_receipt_id', 'purchase_order_item_id', 'qty_received', 'qty_rejected', 'notes'
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function receipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }
}
