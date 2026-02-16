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
            'notes' => 'nullable|string',
            // Face & Location Verification
            'face_image' => 'nullable|image|max:5120', // 5MB max
            'office_location_id' => 'nullable|exists:office_locations,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }
}
