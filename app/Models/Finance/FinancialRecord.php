<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialRecord extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'type',
        'category',
        'amount',
        'record_date',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'record_date' => 'date',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
