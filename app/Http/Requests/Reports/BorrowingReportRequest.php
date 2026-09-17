<?php

namespace App\Http\Requests\Reports;

use App\Enums\BorrowRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BorrowingReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('report.borrowing') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(BorrowRequestStatus::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }
}
