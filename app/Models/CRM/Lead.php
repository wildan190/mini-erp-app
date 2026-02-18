<?php

namespace App\Models\CRM;


use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
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
    protected $fillable = [
        'uuid',
        'lead_name',
        'email',
        'phone',
        'company',
        'source',
        'status',
        'notes'
    ];
}