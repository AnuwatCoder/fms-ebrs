<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class OverdueReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('report.overdue') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
