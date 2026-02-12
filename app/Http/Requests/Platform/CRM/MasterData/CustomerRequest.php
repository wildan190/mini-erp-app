<?php

namespace App\Http\Requests\Platform\CRM\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('customers', 'email')->ignore($this->route('id')),
            ],
            'company_name' => 'nullable|string|max:255',
            'customer_type' => 'required|in:corporate,individual',
            'tax_id' => 'nullable|string|max:50',
            'industry' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:20',
            'alt_phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:100',
            'currency' => 'nullable|string|size:3',
            'segment' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,blocked',
            'notes' => 'nullable|string',
        ];
    }
}
