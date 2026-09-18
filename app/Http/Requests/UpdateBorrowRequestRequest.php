<?php

namespace App\Http\Requests;

class UpdateBorrowRequestRequest extends StoreBorrowRequestRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('borrowRequest')) === true;
    }
}
