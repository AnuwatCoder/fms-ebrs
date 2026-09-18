<?php

namespace App\Http\Requests;

use App\Models\BorrowRequest;
use Illuminate\Foundation\Http\FormRequest;

class CancelBorrowRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $borrowRequest = $this->route('borrowRequest');

        return $borrowRequest instanceof BorrowRequest
            && $this->user()?->can('cancel', $borrowRequest) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
