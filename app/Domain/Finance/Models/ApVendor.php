<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApVendor extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'ap_vendors';

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'npwp',
        'bank_code',
        'bank_account_number',
        'bank_account_name',
        'is_active',
        'midtrans_beneficiary_alias',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function bills()
    {
        return $this->hasMany(ApBill::class, 'vendor_id');
    }
}
