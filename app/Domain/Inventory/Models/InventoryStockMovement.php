<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InventoryStockMovement extends Model
{
    use HasFactory;

    protected $table = 'inventory_stock_movements';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'product_uuid',
        'warehouse_uuid',
        'type',
        'quantity',
        'stock_after',
        'reference_number',
        'notes',
        'created_by_uuid',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'product_uuid', 'uuid');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_uuid', 'uuid');
    }
}
