<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ApPayment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ap_payments';

    protected $fillable = [
        'uuid',
        'ap_bill_id',
        'payment_date',
        'amount',
        'payment_method',
        'midtrans_reference_no',
        'midtrans_beneficiary_alias',
        'midtrans_status',
        'midtrans_response',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'payment_date'       => 'date',
        'amount'             => 'decimal:2',
        'midtrans_response'  => 'array',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function bill()
    {
        return $this->belongsTo(ApBill::class, 'ap_bill_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
