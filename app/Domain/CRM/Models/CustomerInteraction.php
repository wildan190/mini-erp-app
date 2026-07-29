<?php

namespace App\Domain\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerInteraction extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'notes',
        'contacted_by',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
