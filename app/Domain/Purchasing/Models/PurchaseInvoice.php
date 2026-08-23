<?php

namespace App\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'number', 'supplier_id', 'purchase_order_id', 'date', 'due_date',
        'notes', 'subtotal', 'tax_amount', 'total_amount', 'status'
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
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
