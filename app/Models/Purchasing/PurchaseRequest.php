<?php

namespace App\Models\Purchasing;

use App\Models\User;
use App\Models\HRM\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'number', 'date', 'requestor_id', 'department_uuid', 'notes', 'status'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_uuid', 'uuid');
    }
}
