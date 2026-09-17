<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessEquipmentCheckoutRequest extends FormRequest
{
    public const ACTION_READY = 'READY_FOR_PICKUP';

    public const ACTION_CHECKOUT = 'CHECKOUT';

    public function authorize(): bool
    {
        return $this->user()?->can('checkout', $this->route('borrowRequest')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in([self::ACTION_READY, self::ACTION_CHECKOUT])],
            'conditions' => [
                Rule::requiredIf($this->string('action')->toString() === self::ACTION_CHECKOUT),
                'nullable',
                'array',
            ],
            'conditions.*' => ['required', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'conditions.required' => 'กรุณาระบุสภาพก่อนจ่ายของอุปกรณ์ทุกรายการ',
            'conditions.*.required' => 'กรุณาระบุสภาพก่อนจ่ายของอุปกรณ์ทุกรายการ',
        ];
    }
}
