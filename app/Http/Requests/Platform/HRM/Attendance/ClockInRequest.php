<?php

namespace App\Http\Requests\Platform\HRM\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class ClockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_lat' => 'nullable|string',
            'location_long' => 'nullable|string',
            'notes' => 'nullable|string|max:255',
        ];
    }
}
