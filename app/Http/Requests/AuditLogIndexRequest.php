<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuditLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('audit.view') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'event' => ['nullable', 'string', 'max:100', 'exists:audit_logs,event'],
            'causer' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }
}
