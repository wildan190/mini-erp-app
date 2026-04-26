<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'type',
        'parent_uuid',
        'is_reconcilable'
    ];

    protected $casts = [
        'is_reconcilable' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_uuid', 'uuid');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_uuid', 'uuid');
    }

    public function journalItems()
    {
        return $this->hasMany(JournalItem::class, 'account_uuid', 'uuid');
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class, 'account_uuid', 'uuid');
    }
}
