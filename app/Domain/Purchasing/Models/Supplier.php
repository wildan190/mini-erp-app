<?php

namespace App\Domain\Purchasing\Models;

use App\Domain\Finance\Models\ApBill;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'pic',
        'contact',
        'email',
        'address',
        'npwp',
        'category',
        'currency_code',
        'is_active',
        // Bank / AP Payment fields
        'bank_code',
        'bank_account_number',
        'bank_account_name',
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

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function invoices()
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    /**
     * Account Payable bills linked to this supplier.
     */
    public function bills()
    {
        return $this->hasMany(ApBill::class, 'vendor_id');
    }

    /**
     * Whether the supplier is configured for AP payments.
     */
    public function getIsApReadyAttribute(): bool
    {
        return filled($this->bank_code)
            && filled($this->bank_account_number)
            && filled($this->bank_account_name);
    }
}
