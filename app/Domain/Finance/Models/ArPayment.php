<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArPayment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'ar_payments';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'ar_invoice_uuid',
        'payment_number',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'bank_account',
        'receipt_attachment_path',
        'notes',
        'recorded_by_user_id',
        'recorded_by_name',
        'status',
        'finance_record_uuid',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(ArInvoice::class, 'ar_invoice_uuid', 'uuid');
    }

    public function financialRecord()
    {
        return $this->belongsTo(FinancialRecord::class, 'finance_record_uuid', 'uuid');
    }
}
