<?php

namespace App\Domain\HRM\Requests\Designation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDesignationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255|unique:designations,name,' . $this->route('uuid') . ',uuid',
            'description' => 'nullable|string',
        ];
    }
}
