<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalItem extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'entry_uuid',
        'account_uuid',
        'debit',
        'credit',
        'balance',
        'analytical_account_uuid',
        'description'
    ];

    public function entry()
    {
        return $this->belongsTo(JournalEntry::class, 'entry_uuid', 'uuid');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_uuid', 'uuid');
    }

    public function analyticalAccount()
    {
        return $this->belongsTo(AnalyticalAccount::class, 'analytical_account_uuid', 'uuid');
    }
}
