<?php

namespace App\Domain\HRM\Requests\Resignation;

use Illuminate\Foundation\Http\FormRequest;

class StoreResignationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employee_uuid'     => 'nullable|exists:employees,uuid',
            'notice_date'       => 'required|date',
            'resignation_date'  => [
                'required',
                'date',
                'after_or_equal:' . \Carbon\Carbon::parse($this->input('notice_date', now()))->addDays(30)->toDateString(),
            ],
            'reason'            => 'required|string',
            'handover_to_uuid'  => 'nullable|exists:employees,uuid',
        ];
    }

    public function messages(): array
    {
        return [
            'resignation_date.after_or_equal' => 'Pengajuan resign harus disubmit minimal 30 hari sebelum tanggal efektif pengunduran diri (30 Days Notice Period).',
        ];
    }
}
