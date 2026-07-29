<?php

namespace App\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'item_name', 'qty', 'price', 'tax_rate', 'discount', 'total'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }
}
