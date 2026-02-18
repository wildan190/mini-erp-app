<?php

namespace App\Models\HRM;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

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
        'user_id',
        'department_id',
        'designation_id',
        'shift_id',
        'emp_code',
        'first_name',
        'last_name',
        'phone',
        'gender',
        'marital_status',
        'joining_date',
        'status',
        'photo',
        // Extended fields
        'nik',
        'place_of_birth',
        'date_of_birth',
        'religion',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        // Payroll
        'basic_salary',
        'bank_name',
        'bank_account_number',
        // Face Recognition
        'face_encoding',
        'face_image_path',
        'requires_face_verification',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function employmentHistories()
    {
        return $this->hasMany(EmploymentHistory::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
