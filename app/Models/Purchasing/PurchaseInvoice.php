<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PurchaseInvoice extends Model
{
    protected $fillable = [
        'number', 'supplier_id', 'purchase_order_id', 'goods_receipt_id', 'date', 'due_date',
        'subtotal', 'tax_amount', 'total_amount', 'status', 'journal_entry_uuid'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }
}
