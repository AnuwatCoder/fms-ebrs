<?php

namespace App\Http\Requests;

use App\Models\BorrowRequest;
use App\Models\SystemSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBorrowRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BorrowRequest::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $maxItems = (int) SystemSetting::read('max_items_per_request');

        return [
            'purpose' => ['required', 'string', 'max:2000'],
            'usage_location' => ['nullable', 'string', 'max:255'],
            'borrow_date' => ['required', 'date', 'after_or_equal:today'],
            'expected_return_date' => ['required', 'date', 'after_or_equal:borrow_date'],
            'note' => ['nullable', 'string', 'max:2000'],
            'equipment_ids' => ['required', 'array', 'min:1', 'max:'.$maxItems],
            'equipment_ids.*' => ['required', 'integer', 'distinct', 'exists:equipment,id'],
            'accept_terms' => ['required', 'accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'purpose.required' => 'กรุณาระบุวัตถุประสงค์การยืม',
            'borrow_date.after_or_equal' => 'วันที่ยืมต้องไม่เป็นวันที่ในอดีต',
            'expected_return_date.after_or_equal' => 'วันที่คืนต้องไม่น้อยกว่าวันที่ยืม',
            'equipment_ids.required' => 'กรุณาเลือกอุปกรณ์อย่างน้อย 1 รายการ',
            'equipment_ids.min' => 'กรุณาเลือกอุปกรณ์อย่างน้อย 1 รายการ',
            'equipment_ids.max' => 'เลือกอุปกรณ์ได้ไม่เกิน :max รายการต่อคำขอ',
            'accept_terms.required' => 'กรุณายอมรับข้อตกลงและเงื่อนไขการยืมก่อนส่งคำขอ',
            'accept_terms.accepted' => 'กรุณายอมรับข้อตกลงและเงื่อนไขการยืมก่อนส่งคำขอ',
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('borrow_date') || SystemSetting::read('allow_weekend_borrow')) {
                return;
            }

            if ($this->date('borrow_date')?->isWeekend()) {
                $validator->errors()->add(
                    'borrow_date',
                    'ระบบไม่อนุญาตให้เลือกวันยืมในวันหยุดสุดสัปดาห์',
                );
            }
        }];
    }
}
