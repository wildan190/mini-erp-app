<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'inventory_warehouses';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

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
