<?php

namespace App\Http\Requests\Platform\HRM\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:salary_components,name',
            'type' => 'required|in:earning,deduction',
            'is_taxable' => 'boolean',
            'is_fixed' => 'boolean',
            'value' => 'required|numeric|min:0',
            'percentage_of' => 'nullable|required_if:is_fixed,false|in:basic_salary',
        ];
    }
}
