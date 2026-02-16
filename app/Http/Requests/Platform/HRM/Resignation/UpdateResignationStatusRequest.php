<?php

namespace App\Http\Requests\Platform\HRM\Resignation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResignationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['approved', 'rejected', 'withdrawn'])],
            'remarks' => 'nullable|string',
        ];
    }
}
