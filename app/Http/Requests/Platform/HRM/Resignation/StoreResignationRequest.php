<?php

namespace App\Http\Requests\Platform\HRM\Resignation;

use Illuminate\Foundation\Http\FormRequest;

class StoreResignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notice_date' => 'required|date',
            'resignation_date' => 'required|date|after_or_equal:notice_date',
            'reason' => 'required|string',
            'handover_to_uuid' => 'nullable|exists:employees,uuid',
        ];
    }
}
