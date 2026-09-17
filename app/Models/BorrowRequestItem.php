<?php

namespace App\Models;

use App\Enums\BorrowItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BorrowRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrow_request_id',
        'equipment_id',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return ['status' => BorrowItemStatus::class];
    }

    /** @return BelongsTo<BorrowRequest, $this> */
    public function borrowRequest(): BelongsTo
    {
        return $this->belongsTo(BorrowRequest::class);
    }

    /** @return BelongsTo<Equipment, $this> */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /** @return HasOne<EquipmentCheckout, $this> */
    public function checkout(): HasOne
    {
        return $this->hasOne(EquipmentCheckout::class);
    }

    /** @return HasOne<EquipmentReturn, $this> */
    public function equipmentReturn(): HasOne
    {
        return $this->hasOne(EquipmentReturn::class);
    }
}
