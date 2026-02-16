<?php

namespace App\Models\CRM;


use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use HasUuids;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
    protected $fillable = [
        'uuid',
        'customer_id',
        'quotation_number',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'valid_until',
        'terms'
    ];


    protected $casts = [
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];


    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}