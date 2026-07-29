<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CashForecast extends Model
{
    use HasUuids;

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'forecast_date',
        'predicted_inflow',
        'predicted_outflow',
        'predicted_balance',
        'confidence_score',
        'model_type',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'predicted_inflow' => 'decimal:2',
        'predicted_outflow' => 'decimal:2',
        'predicted_balance' => 'decimal:2',
        'confidence_score' => 'decimal:2',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
