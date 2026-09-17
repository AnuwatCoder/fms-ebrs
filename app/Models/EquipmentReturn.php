<?php

namespace App\Models;

use App\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrow_request_item_id',
        'received_by',
        'returned_at',
        'condition_after',
        'return_status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'returned_at' => 'datetime',
            'return_status' => ReturnStatus::class,
        ];
    }

    /** @return BelongsTo<BorrowRequestItem, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(BorrowRequestItem::class, 'borrow_request_item_id');
    }

    /** @return BelongsTo<User, $this> */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
