<?php

namespace App\Http\Requests\Platform\CRM\ProspectManagement;

use Illuminate\Foundation\Http\FormRequest;

class ProspectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,uuid',
            'title' => 'required|string|max:255',
            'status' => 'required|string|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'expected_value' => 'nullable|numeric|min:0',
            'expected_closing_date' => 'nullable|date',
            'probability' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ];
    }
}
