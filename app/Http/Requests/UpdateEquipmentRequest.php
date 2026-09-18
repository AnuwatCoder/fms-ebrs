<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('equipment')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $equipment = $this->route('equipment');

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('equipment_categories', 'id')
                    ->where(function ($query) use ($equipment): void {
                        $query->where('active', true)
                            ->orWhere('id', $equipment->category_id);
                    })
                    ->whereNull('deleted_at'),
            ],
            'asset_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('equipment', 'asset_number')->ignore($equipment->getKey()),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('equipment', 'serial_number')->ignore($equipment->getKey()),
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
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
