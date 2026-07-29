<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasUuids;

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'product_uuid',
        'quantity',
        'uom',
        'type',
        'stock_level_after',
        'recorded_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'stock_level_after' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
