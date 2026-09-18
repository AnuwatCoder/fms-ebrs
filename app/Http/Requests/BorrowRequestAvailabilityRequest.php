<?php

namespace App\Http\Requests;

use App\Models\BorrowRequest;
use App\Models\SystemSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BorrowRequestAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BorrowRequest::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'borrow_date' => ['required', 'date', 'after_or_equal:today'],
            'expected_return_date' => ['required', 'date', 'after_or_equal:borrow_date'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'borrow_date.required' => 'กรุณาเลือกวันที่ยืม',
            'borrow_date.after_or_equal' => 'วันที่ยืมต้องไม่เป็นวันที่ในอดีต',
            'expected_return_date.required' => 'กรุณาเลือกวันที่คืน',
            'expected_return_date.after_or_equal' => 'วันที่คืนต้องไม่น้อยกว่าวันที่ยืม',
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
