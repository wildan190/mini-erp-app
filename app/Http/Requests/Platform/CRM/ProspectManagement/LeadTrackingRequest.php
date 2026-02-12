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
            'lead_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'source' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}