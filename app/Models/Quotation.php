<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Quotation extends Model
{
    protected $fillable = ['customer_id', 'amount', 'valid_until'];


    protected $casts = [
        'valid_until' => 'date'
    ];


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}