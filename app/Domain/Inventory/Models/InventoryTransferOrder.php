<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransferOrder extends Model
{
    use HasFactory, HasUuids;

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

    public function uniqueIds(): array
    {
        return ['uuid'];
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
