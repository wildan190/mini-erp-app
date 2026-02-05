<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class SalesPipeline extends Model
{
    protected $fillable = ['prospect_id', 'stage'];


    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }
}