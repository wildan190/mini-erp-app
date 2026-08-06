<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InventoryTransferOrderItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_transfer_order_items';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'transfer_order_uuid',
        'product_uuid',
        'quantity_requested',
        'quantity_shipped',
        'quantity_received',
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

    public function transferOrder()
    {
        return $this->belongsTo(InventoryTransferOrder::class, 'transfer_order_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'product_uuid', 'uuid');
    }
}
