<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Prospect extends Model
{
    protected $fillable = ['customer_id', 'status'];


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }


    public function pipelines(): HasMany
    {
        return $this->hasMany(SalesPipeline::class);
    }
}