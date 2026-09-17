<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentCheckout extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrow_request_item_id',
        'checked_out_by',
        'checked_out_at',
        'condition_before',
        'note',
    ];

    protected function casts(): array
    {
        return ['checked_out_at' => 'datetime'];
    }

    /** @return BelongsTo<BorrowRequestItem, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(BorrowRequestItem::class, 'borrow_request_item_id');
    }

    /** @return BelongsTo<User, $this> */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }
}
