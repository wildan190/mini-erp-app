<?php

namespace App\Http\Requests\Platform\CRM\ProspectManagement;

use Illuminate\Foundation\Http\FormRequest;

class SalesPipeLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prospect_id' => 'required|uuid|exists:prospects,uuid',
            'stage' => 'required|string|max:255',
            'notes' => 'nullable|string'
        ];
    }
}