<?php

namespace App\Http\Requests\Platform\HRM\Reimbursement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReimbursementStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['approved', 'rejected', 'paid'])],
            'reason' => 'nullable|string',
        ];
    }
}
