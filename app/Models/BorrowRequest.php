<?php

namespace App\Models;

use App\Enums\BorrowRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BorrowRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_no',
        'user_id',
        'purpose',
        'usage_location',
        'borrow_date',
        'expected_return_date',
        'status',
        'note',
        'submitted_at',
        'terms_accepted_at',
        'terms_version',
    ];

    protected function casts(): array
    {
        return [
            'borrow_date' => 'date',
            'expected_return_date' => 'date',
            'status' => BorrowRequestStatus::class,
            'submitted_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<BorrowRequestItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(BorrowRequestItem::class);
    }

    /** @return HasMany<BorrowApproval, $this> */
    public function approvals(): HasMany
    {
        return $this->hasMany(BorrowApproval::class);
    }

    /** @return HasMany<EquipmentIncident, $this> */
    public function incidents(): HasMany
    {
        return $this->hasMany(EquipmentIncident::class);
    }
}
