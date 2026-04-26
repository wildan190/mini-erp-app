<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'entry_number',
        'date',
        'reference',
        'description',
        'status'
    ];

    public function items()
    {
        return $this->hasMany(JournalItem::class, 'entry_uuid', 'uuid');
    }
}
