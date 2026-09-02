<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArInvoiceItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ar_invoice_items';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'ar_invoice_uuid',
        'item_name',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_rate',
        'tax_rate',
        'amount',
    ];

    protected $casts = [
        'quantity'      => 'decimal:2',
        'unit_price'    => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'tax_rate'      => 'decimal:2',
        'amount'        => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(ArInvoice::class, 'ar_invoice_uuid', 'uuid');
    }
}
