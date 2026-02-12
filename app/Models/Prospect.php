<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prospect extends Model
{
    use HasUuids;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
    protected $fillable = [
        'uuid',
        'customer_id',
        'title',
        'status',
        'expected_value',
        'expected_closing_date',
        'probability',
        'notes'
    ];


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }


    public function pipelines(): HasMany
    {
        return $this->hasMany(SalesPipeline::class);
    }
}