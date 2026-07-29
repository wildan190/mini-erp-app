<?php

namespace App\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'supplier_id', 'purchase_order_id', 'date', 'due_date',
        'notes', 'subtotal', 'tax_amount', 'total_amount', 'status'
    ];

    protected static function newFactory()
    {
        return \Database\Factories\Purchasing\PurchaseInvoiceFactory::new();
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
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
