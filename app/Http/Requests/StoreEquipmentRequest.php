<?php

namespace App\Http\Requests;

use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Equipment::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('equipment_categories', 'id')
                    ->where('active', true)
                    ->whereNull('deleted_at'),
            ],
            'asset_number' => ['nullable', 'string', 'max:100', 'unique:equipment,asset_number'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255', 'unique:equipment,serial_number'],
            'location' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::enum(EquipmentStatus::class)],
            'active' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'กรุณาเลือกหมวดหมู่อุปกรณ์ที่เปิดใช้งาน',
            'name.required' => 'กรุณาระบุชื่ออุปกรณ์',
            'asset_number.unique' => 'เลขครุภัณฑ์นี้ถูกใช้งานแล้ว',
            'serial_number.unique' => 'Serial number นี้ถูกใช้งานแล้ว',
            'purchase_date.before_or_equal' => 'วันที่ซื้อต้องไม่เป็นวันที่ในอนาคต',
        ];
    }
}
