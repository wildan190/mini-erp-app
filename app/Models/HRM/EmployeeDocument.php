<?php

namespace App\Models\HRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'type',
        'file_path',
        'expiry_date',
        'description',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
