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
            'user_uuid' => [
                'nullable', 
                'exists:users,uuid',
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::where('uuid', $value)->first();
                    if ($user && \App\Models\HRM\Employee::where('user_id', $user->id)->exists()) {
                        $fail('The user already has an employee profile.');
                    }
                }
            ],
            'first_name' => 'required_without:user_uuid|string|max:255',
            'last_name' => 'required_without:user_uuid|string|max:255',
            'email' => 'required_without:user_uuid|nullable|email|max:255|unique:users,email',
            'password' => 'required_without:user_uuid|nullable|string|min:8',
            'department_uuid' => 'nullable|exists:departments,uuid',
            'designation_uuid' => 'nullable|exists:designations,uuid',
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
