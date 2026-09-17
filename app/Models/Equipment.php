<?php

namespace App\Models;

use App\Enums\EquipmentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'equipment';

    protected $fillable = [
        'category_id',
        'equipment_code',
        'asset_number',
        'name',
        'description',
        'brand',
        'model',
        'serial_number',
        'location',
        'purchase_date',
        'image_path',
        'status',
        'active',
    ];

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'status' => EquipmentStatus::class,
            'active' => 'boolean',
        ];
    }

    /** @return BelongsTo<EquipmentCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    /** @return HasMany<BorrowRequestItem, $this> */
    public function borrowItems(): HasMany
    {
        return $this->hasMany(BorrowRequestItem::class);
    }

    /** @return HasMany<EquipmentIncident, $this> */
    public function incidents(): HasMany
    {
        return $this->hasMany(EquipmentIncident::class);
    }
}
