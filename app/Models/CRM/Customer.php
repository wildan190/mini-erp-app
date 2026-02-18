<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasUuids;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
    const TYPE_CORPORATE = 'corporate';
    const TYPE_INDIVIDUAL = 'individual';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'company_name',
        'customer_type',
        'tax_id',
        'industry',
        'website',
        'phone',
        'alt_phone',
        'department',
        'billing_address',
        'shipping_address',
        'city',
        'province',
        'postal_code',
        'country',
        'credit_limit',
        'payment_terms',
        'currency',
        'segment',
        'status',
        'notes'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }
}