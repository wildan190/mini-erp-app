<?php

namespace App\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'purchase_invoice_id', 'item_name', 'qty', 'price', 'total'
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }
}
