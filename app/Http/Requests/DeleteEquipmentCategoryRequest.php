<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteEquipmentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('delete', $this->route('category')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
