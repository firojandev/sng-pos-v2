<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Observers\AuditObserver;
use Modules\Employee\Models\Employee;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'username', 'email', 'phone', 'avatar', 'password', 'pin', 'support_pin', 'shop_id', 'email_verified_at'])]
#[Hidden(['password', 'pin', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasRoles {
        assignRole as protected spatieAssignRole;
        syncRoles as protected spatieSyncRoles;
        removeRole as protected spatieRemoveRole;
    }

    /**
     * Get the user's avatar URL.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (! $this->avatar) {
                    return null;
                }

                if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
                    return $this->avatar;
                }

                $clean = ltrim($this->avatar, '/');
                $path = str_starts_with($clean, 'storage/') ? $clean : 'storage/'.$clean;

                return asset($path);
            }
        );
    }

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
            'pin' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);

        static::creating(function (User $user) {
            if (empty($user->support_pin)) {
                $user->support_pin = static::generateUniqueSupportPin();
            }
        });

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
     * Generate a unique 6-digit support PIN.
     */
    public static function generateUniqueSupportPin(): string
    {
        do {
            $pin = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('support_pin', $pin)->exists());

        return $pin;
    }

    /**
     * Find a user by email, username, or phone number.
     */
    public static function findByIdentifier(string $identifier): ?static
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $identifier);

        return static::where(function ($query) use ($identifier, $cleanPhone) {
            $query->where('email', $identifier)
                ->orWhere('username', $identifier);

            if (! empty($cleanPhone) && strlen($cleanPhone) >= 6) {
                $query->orWhere('phone', $identifier)
                    ->orWhere('phone', $cleanPhone);
            }
        })->first();
    }

    /**
     * Check if the given secret matches the user's password, PIN, or support PIN.
     *
     * @return 'password'|'pin'|'support_pin'|false
     */
    public function verifySecret(string $secret): string|false
    {
        // 1. Password verification
        if (! empty($this->password) && Hash::check($secret, $this->password)) {
            return 'password';
        }

        // 2. User 4-digit PIN verification
        if (! empty($this->pin) && strlen($secret) === 4 && Hash::check($secret, $this->pin)) {
            return 'pin';
        }

        // 3. Support Team 6-digit PIN verification
        if (! empty($this->support_pin) && strlen($secret) === 6 && hash_equals((string) $this->support_pin, $secret)) {
            return 'support_pin';
        }

        return false;
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
     * Check whether this user is registered as a shop owner.
     */
    public function isShopOwner(?int $shopId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return false;
        }

        if ($shopId) {
            return $this->shops()->where('shops.id', $shopId)->wherePivot('is_owner', true)->exists();
        }

        if ($this->relationLoaded('shops')) {
            $isOwner = $this->shops->contains(fn ($s) => (bool) ($s->pivot?->is_owner ?? false));
            if ($isOwner) {
                return true;
            }
        }

        return $this->shops()->wherePivot('is_owner', true)->exists();
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

        setPermissionsTeamId($shopId);
        $this->unsetRelation('roles');
        $this->unsetRelation('permissions');

        return true;
    }

    public function isSuperAdmin(): bool
    {
        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.model_type', static::class)
            ->where('roles.name', 'Super Admin')
            ->exists();
    }

    protected function resolvePermissionsTeamId(mixed ...$roles): int|string
    {
        foreach ($roles as $role) {
            if ($role instanceof Role && ! empty($role->shop_id)) {
                return $role->shop_id;
            }
            if (is_array($role)) {
                foreach ($role as $r) {
                    if ($r instanceof Role && ! empty($r->shop_id)) {
                        return $r->shop_id;
                    }
                }
            }
        }

        $currentTeamId = getPermissionsTeamId();
        if ($currentTeamId !== null && $currentTeamId !== 0 && $currentTeamId !== '') {
            return $currentTeamId;
        }

        return $this->shop_id ?? 0;
    }

    public function assignRole(...$roles): static
    {
        $previousTeamId = getPermissionsTeamId();
        $targetTeamId = $this->resolvePermissionsTeamId(...$roles);
        setPermissionsTeamId($targetTeamId);

        try {
            return $this->spatieAssignRole(...$roles);
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }

    public function syncRoles(...$roles): static
    {
        $previousTeamId = getPermissionsTeamId();
        $targetTeamId = $this->resolvePermissionsTeamId(...$roles);
        setPermissionsTeamId($targetTeamId);

        try {
            return $this->spatieSyncRoles(...$roles);
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }

    public function removeRole($role): static
    {
        $previousTeamId = getPermissionsTeamId();
        $targetTeamId = $this->resolvePermissionsTeamId($role);
        setPermissionsTeamId($targetTeamId);

        try {
            return $this->spatieRemoveRole($role);
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }
}
