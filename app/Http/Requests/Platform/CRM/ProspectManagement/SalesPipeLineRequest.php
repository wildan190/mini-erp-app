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
            'prospect_id' => 'required|integer',
            'stage' => 'required|string'
        ];
    }
}