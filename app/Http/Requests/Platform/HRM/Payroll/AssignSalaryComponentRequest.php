<?php

namespace App\Http\Requests\Platform\HRM\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class AssignSalaryComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('custom_value') && $this->custom_value === '') {
            $this->merge(['custom_value' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'salary_component_uuid' => 'required|string|exists:salary_components,uuid',
            'custom_value'          => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'salary_component_uuid.exists' => 'Salary component not found.',
        ];
    }
}
