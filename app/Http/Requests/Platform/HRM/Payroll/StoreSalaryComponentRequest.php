<?php

namespace App\Http\Requests\Platform\HRM\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $merge = [];
        if ($this->has('is_taxable')) {
            $merge['is_taxable'] = filter_var($this->is_taxable, FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->has('is_fixed')) {
            $merge['is_fixed'] = filter_var($this->is_fixed, FILTER_VALIDATE_BOOLEAN);
        }
        if (!empty($merge)) {
            $this->merge($merge);
        }
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
