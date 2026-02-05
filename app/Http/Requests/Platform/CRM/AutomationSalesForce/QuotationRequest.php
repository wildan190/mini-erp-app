<?php

namespace App\Http\Requests\Platform\CRM\AutomationSalesForce;


use Illuminate\Foundation\Http\FormRequest;


class QuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer',
            'amount' => 'required|numeric',
            'valid_until' => 'required|date'
        ];
    }
}