<?php

namespace App\Http\Requests\Platform\HRM\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')->id;
        return [
            'user_id' => 'required|exists:users,id|unique:employees,user_id,' . $employeeId,
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'emp_code' => 'nullable|string|max:50|unique:employees,emp_code,' . $employeeId,
            'joining_date' => 'nullable|date',
            'status' => ['nullable', Rule::in(['active', 'inactive', 'terminated', 'resigned'])],
            'nik' => 'nullable|string|unique:employees,nik,' . $employeeId,
            'place_of_birth' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'religion' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ];
    }
}
