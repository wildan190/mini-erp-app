<?php

namespace App\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'pic', 'contact', 'address', 'npwp', 'category', 'currency_code', 'is_active'
    ];

    protected static function newFactory()
    {
        return \Database\Factories\Purchasing\SupplierFactory::new();
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function invoices()
    {
        return $this->hasMany(PurchaseInvoice::class);
    }
}
