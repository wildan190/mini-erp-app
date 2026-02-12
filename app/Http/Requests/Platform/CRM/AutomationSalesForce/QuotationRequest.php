<?php

namespace App\Http\Requests\Platform\CRM\AutomationSalesForce;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|string|exists:customers,uuid',
            'quotation_number' => [
                'nullable',
                'string',
                Rule::unique('quotations', 'quotation_number')->ignore($this->route('id')),
            ],
            'valid_until' => 'required|date',
            'discount_amount' => 'nullable|numeric|min:0',
            'terms' => 'nullable|string',
            'status' => 'nullable|string|in:draft,sent,accepted,declined,expired',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }
}