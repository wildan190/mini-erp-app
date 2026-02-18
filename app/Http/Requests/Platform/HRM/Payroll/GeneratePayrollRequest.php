<?php

namespace App\Http\Requests\Platform\HRM\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class GeneratePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payroll_period_uuid' => 'required|exists:payroll_periods,uuid',
        ];
    }
}
