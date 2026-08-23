<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InventoryProduct extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = 'inventory_products';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'category_uuid',
        'sku',
        'barcode',
        'name',
        'description',
        'uom',
        'unit_cost',
        'selling_price',
        'reorder_level',
        'min_stock',
        'max_stock',
        'is_active',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->sku)) {
                $model->sku = 'SKU-' . strtoupper(Str::random(7));
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_uuid', 'uuid');
    }

    public function stocks()
    {
        return $this->hasMany(InventoryStock::class, 'product_uuid', 'uuid');
    }

    public function movements()
    {
        return $this->hasMany(InventoryStockMovement::class, 'product_uuid', 'uuid');
    }
}
