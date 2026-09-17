<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'active',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /** @return HasMany<UserIdentity, $this> */
    public function identities(): HasMany
    {
        return $this->hasMany(UserIdentity::class);
    }

    /** @return HasMany<BorrowRequest, $this> */
    public function borrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class);
    }

    /** @return HasMany<EquipmentCheckout, $this> */
    public function processedCheckouts(): HasMany
    {
        return $this->hasMany(EquipmentCheckout::class, 'checked_out_by');
    }

    /** @return HasMany<EquipmentReturn, $this> */
    public function processedReturns(): HasMany
    {
        return $this->hasMany(EquipmentReturn::class, 'received_by');
    }
}
