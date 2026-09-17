<?php

namespace App\Http\Requests;

use App\Enums\ReturnStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessEquipmentReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('receiveReturn', $this->route('borrowRequest')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.return_status' => ['required', Rule::enum(ReturnStatus::class)],
            'items.*.condition_after' => ['required', 'string', 'max:2000'],
            'items.*.note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'items.required' => 'ไม่พบรายการอุปกรณ์สำหรับรับคืน',
            'items.*.return_status.required' => 'กรุณาระบุผลการตรวจรับอุปกรณ์ทุกรายการ',
            'items.*.condition_after.required' => 'กรุณาระบุสภาพหลังคืนของอุปกรณ์ทุกรายการ',
        ];
    }
}
