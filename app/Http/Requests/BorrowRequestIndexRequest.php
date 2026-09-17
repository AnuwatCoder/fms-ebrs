<?php

namespace App\Http\Requests;

use App\Enums\BorrowRequestStatus;
use App\Models\BorrowRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BorrowRequestIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->routeIs('borrow.mine')) {
            return $this->user()?->can('borrow.view-own') === true;
        }

        return $this->user()?->can('viewAny', BorrowRequest::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(BorrowRequestStatus::class)],
        ];
    }
}
