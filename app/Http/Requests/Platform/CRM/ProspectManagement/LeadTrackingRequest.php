<?php

namespace App\Http\Requests\Platform\CRM\ProspectManagement;


use Illuminate\Foundation\Http\FormRequest;


class LeadTrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'lead_name' => 'required|string',
            'source' => 'required|string'
        ];
    }
}