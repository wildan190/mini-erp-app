<?php

namespace App\Http\Requests\Platform\HRM\EmployeeDocument;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['ktp', 'npwp', 'contract', 'certificate', 'other'])],
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // Max 2MB
            'expiry_date' => 'nullable|date',
            'description' => 'nullable|string',
        ];
    }
}
