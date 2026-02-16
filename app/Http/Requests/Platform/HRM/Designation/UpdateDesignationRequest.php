<?php

namespace App\Http\Requests\Platform\HRM\Designation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:designations,name,' . $this->route('designation')->id,
            'description' => 'nullable|string',
        ];
    }
}
