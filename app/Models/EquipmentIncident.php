<?php

namespace App\Models;

use App\Enums\IncidentState;
use App\Enums\IncidentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'borrow_request_id',
        'type',
        'description',
        'reported_by',
        'reported_at',
        'resolved_at',
        'resolved_by',
        'resolution',
    ];

    protected function casts(): array
    {
        return [
            'type' => IncidentType::class,
            'reported_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function state(): IncidentState
    {
        return $this->resolved_at === null
            ? IncidentState::Open
            : IncidentState::Resolved;
    }

    /** @return BelongsTo<Equipment, $this> */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /** @return BelongsTo<BorrowRequest, $this> */
    public function borrowRequest(): BelongsTo
    {
        return $this->belongsTo(BorrowRequest::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /** @return BelongsTo<User, $this> */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
