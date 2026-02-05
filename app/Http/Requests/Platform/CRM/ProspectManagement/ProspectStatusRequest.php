<?php

namespace App\Http\Requests\Platform\CRM\ProspectManagement;


use Illuminate\Foundation\Http\FormRequest;


class ProspectStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'status' => 'required|string|in:new,contacted,qualified,proposal,negotiation,won,lost'
        ];
    }
}