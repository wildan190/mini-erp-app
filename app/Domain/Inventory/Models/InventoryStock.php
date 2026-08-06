<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InventoryStock extends Model
{
    use HasFactory;

    protected $table = 'inventory_stocks';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'warehouse_uuid',
        'product_uuid',
        'quantity_on_hand',
        'quantity_reserved',
        'quantity_available',
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

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'product_uuid', 'uuid');
    }
}
