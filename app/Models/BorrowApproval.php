<?php

namespace App\Models;

use App\Enums\ApprovalAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrow_request_id',
        'approver_id',
        'action',
        'comment',
        'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => ApprovalAction::class,
            'acted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<BorrowRequest, $this> */
    public function borrowRequest(): BelongsTo
    {
        return $this->belongsTo(BorrowRequest::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
