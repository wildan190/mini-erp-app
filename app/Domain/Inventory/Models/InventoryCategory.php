<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InventoryCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_categories';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->code)) {
                $model->code = 'CAT-' . strtoupper(Str::random(5));
            }
        });
    }

    public function products()
    {
        return $this->hasMany(InventoryProduct::class, 'category_uuid', 'uuid');
    }
}
