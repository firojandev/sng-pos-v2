<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Employee\Models\Employee;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'shop_id', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if ($user->shop_id && ! $user->isSuperAdmin()) {
                $user->shops()->syncWithoutDetaching([
                    $user->shop_id => [
                        'role' => $user->roles->first()?->name ?? 'Admin',
                        'is_owner' => true,
                    ],
                ]);
            }
        });
    }

    /**
     * The active / current shop for this user.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Linked employee profile for this user.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Alias for the active / current shop.
     */
    public function currentShop(): BelongsTo
    {
        return $this->shop();
    }

    /**
     * All shops accessible by this user.
     */
    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_user')
            ->withPivot('role', 'is_owner')
            ->withTimestamps();
    }

    /**
     * All active shops accessible by this user.
     */
    public function activeShops(): BelongsToMany
    {
        return $this->shops()->where('shops.status', 'active');
    }

    /**
     * Check whether this user has multiple accessible active shops.
     */
    public function hasMultipleShops(): bool
    {
        return $this->shops()->where('shops.status', 'active')->count() > 1;
    }

    /**
     * Check whether the user belongs to a specific shop.
     */
    public function belongsToShop(Shop|int $shop): bool
    {
        $shopId = $shop instanceof Shop ? $shop->id : (int) $shop;

        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->shop_id === $shopId) {
            return true;
        }

        return $this->shops()->where('shops.id', $shopId)->exists();
    }

    /**
     * Switch current active shop to the given shop.
     */
    public function switchShop(Shop|int $shop): bool
    {
        $shopId = $shop instanceof Shop ? $shop->id : (int) $shop;

        if (! $this->belongsToShop($shopId)) {
            return false;
        }

        $this->shop_id = $shopId;
        $this->save();

        session(['current_shop_id' => $shopId]);

        return true;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }
}
