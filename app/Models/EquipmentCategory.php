<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'active',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    /** @return HasMany<Equipment, $this> */
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'category_id');
    }
}
