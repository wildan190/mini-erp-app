<?php

namespace App\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'date', 'requestor_id', 'department_uuid', 'notes', 'status'
    ];

    protected static function newFactory()
    {
        return \Database\Factories\Purchasing\PurchaseRequestFactory::new();
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

    public function requestor()
    {
        return $this->belongsTo(\App\Models\User::class, 'requestor_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }
}
