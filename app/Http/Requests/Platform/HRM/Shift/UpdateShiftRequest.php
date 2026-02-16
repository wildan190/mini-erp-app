<?php

namespace App\Http\Requests\Platform\HRM\Shift;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $shiftId = $this->route('id');
        return [
            'name' => 'required|string|max:50|unique:shifts,name,' . $shiftId,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string|max:255',
        ];
    }
}
