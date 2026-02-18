<?php

namespace App\Http\Requests\Platform\HRM\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id',
            'first_name' => 'required_without:user_id|string|max:255',
            'last_name' => 'required_without:user_id|string|max:255',
            'email' => 'required_without:user_id|nullable|email|max:255|unique:users,email',
            'password' => 'required_without:user_id|nullable|string|min:8',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'emp_code' => 'nullable|string|max:50|unique:employees,emp_code',
            'joining_date' => 'nullable|date',
            'status' => ['nullable', Rule::in(['active', 'inactive', 'terminated', 'resigned'])],
            'nik' => 'nullable|string|unique:employees,nik',
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
