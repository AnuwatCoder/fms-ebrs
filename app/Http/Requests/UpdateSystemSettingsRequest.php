<?php

namespace App\Http\Requests;

use App\Models\SystemSetting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage', SystemSetting::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'organization_name' => ['required', 'string', 'max:255'],
            'default_loan_days' => ['required', 'integer', 'min:1', 'max:365'],
            'max_items_per_request' => ['required', 'integer', 'min:1', 'max:100'],
            'overdue_alert_days' => ['required', 'integer', 'min:0', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'allow_weekend_borrow' => ['required', 'boolean'],
        ];
    }
}
