<?php

namespace App\Http\Requests;

use App\Enums\ApprovalAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessBorrowApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = match ($this->string('action')->toString()) {
            ApprovalAction::Approved->value => 'approve',
            ApprovalAction::Rejected->value => 'reject',
            default => null,
        };

        return $ability !== null
            && $this->user()?->can($ability, $this->route('borrowRequest')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::enum(ApprovalAction::class)],
            'comment' => [
                Rule::requiredIf($this->string('action')->toString() === ApprovalAction::Rejected->value),
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'comment.required' => 'กรุณาระบุเหตุผลเมื่อไม่อนุมัติคำขอ',
        ];
    }
}
