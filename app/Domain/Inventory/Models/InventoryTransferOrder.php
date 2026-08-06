<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InventoryTransferOrder extends Model
{
    use HasFactory;

    protected $table = 'inventory_transfer_orders';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'transfer_number',
        'source_warehouse_uuid',
        'destination_warehouse_uuid',
        'status',
        'transfer_date',
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
            if (empty($model->transfer_number)) {
                $model->transfer_number = 'TRF-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_uuid', 'uuid');
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_uuid', 'uuid');
    }

    public function items()
    {
        return $this->hasMany(InventoryTransferOrderItem::class, 'transfer_order_uuid', 'uuid');
    }
}
