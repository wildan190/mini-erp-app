<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class ApBill extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'ap_bills';

    protected $fillable = [
        'uuid',
        'vendor_id',
        'bill_number',
        'reference',
        'bill_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'status',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'bill_date'   => 'date',
        'due_date'    => 'date',
        'approved_at' => 'datetime',
        'subtotal'    => 'decimal:2',
        'tax_amount'  => 'decimal:2',
        'total_amount'=> 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function vendor()
    {
        return $this->belongsTo(ApVendor::class, 'vendor_id');
    }

    public function items()
    {
        return $this->hasMany(ApBillItem::class, 'ap_bill_id');
    }

    public function payments()
    {
        return $this->hasMany(ApPayment::class, 'ap_bill_id')->orderBy('payment_date');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getOutstandingAmountAttribute(): float
    {
        return (float) $this->total_amount - (float) $this->paid_amount;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date < now() && !in_array($this->status, ['paid', 'cancelled']);
    }
}
