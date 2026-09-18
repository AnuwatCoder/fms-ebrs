<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditBorrowRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('borrowRequest')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
