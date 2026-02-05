<?php

namespace App\Http\Requests\Platform\Auth;


use Illuminate\Foundation\Http\FormRequest;


class PlatformLogoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [];
    }
}