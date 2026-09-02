<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\CRM\Models\Customer;

class ArInvoice extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'ar_invoices';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'invoice_number',
        'customer_uuid',
        'customer_name',
        'customer_email',
        'reference',
        'invoice_date',
        'due_date',
        'payment_terms',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'status',
        'notes',
        'terms_and_conditions',
        'issued_by_user_id',
        'issued_by_name',
        'sent_at',
        'cancelled_at',
        'cancellation_reason',
        'finance_record_uuid',
    ];

    protected $casts = [
        'invoice_date'    => 'date',
        'due_date'        => 'date',
        'sent_at'         => 'datetime',
        'cancelled_at'    => 'datetime',
        'subtotal'        => 'decimal:2',
        'tax_rate'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'paid_amount'     => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_uuid', 'uuid');
    }

    public function items()
    {
        return $this->hasMany(ArInvoiceItem::class, 'ar_invoice_uuid', 'uuid');
    }

    public function payments()
    {
        return $this->hasMany(ArPayment::class, 'ar_invoice_uuid', 'uuid')->orderBy('payment_date', 'desc');
    }

    public function financialRecord()
    {
        return $this->belongsTo(FinancialRecord::class, 'finance_record_uuid', 'uuid');
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }
}
