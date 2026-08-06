<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_warehouses';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'location',
        'address',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->code)) {
                $model->code = 'WH-' . strtoupper(Str::random(5));
            }
        });
    }

    public function stocks()
    {
        return $this->hasMany(InventoryStock::class, 'warehouse_uuid', 'uuid');
    }

    public function movements()
    {
        return $this->hasMany(InventoryStockMovement::class, 'warehouse_uuid', 'uuid');
    }
}
