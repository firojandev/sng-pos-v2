<?php

namespace Modules\Shop\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Finance\Models\Account;
use Revoltify\Subscriptionify\Concerns\InteractsWithSubscriptions;
use Revoltify\Subscriptionify\Contracts\Subscribable;
use Revoltify\Subscriptionify\Enums\Interval;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Shop extends Model implements Subscribable
{
    use InteractsWithSubscriptions {
        hasFeature as protected subscriptionifyHasFeature;
    }

    protected $fillable = [
        'name',
        'slug',
        'store_code',
        'phone',
        'address',
        'status',
    ];

    protected static function booted(): void
    {
        static::created(function (Shop $shop) {
            $adminRole = Role::firstOrCreate([
                'shop_id' => $shop->id,
                'name' => 'Admin',
                'guard_name' => 'web',
            ]);

            $adminRole->syncPermissions(
                Permission::where('guard_name', 'web')->get()
            );
        });
    }

    /**
     * Roles belonging to this shop.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'shop_id');
    }

    /**
     * Users/Admins associated with this shop via pivot table.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_user')
            ->withPivot('role', 'is_owner')
            ->withTimestamps();
    }

    /**
     * Alias for shop administrators.
     */
    public function admins(): BelongsToMany
    {
        return $this->users();
    }

    /**
     * Accounts belonging to this shop.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'shop_id');
    }

    /**
     * The primary cash account of this shop.
     */
    public function cashAccount(): HasOne
    {
        return $this->hasOne(Account::class, 'shop_id')->where('type', 'cash');
    }

    /**
     * Active subscription relationship.
     */
    public function activeSubscription(): MorphOne
    {
        return $this->morphOne(Subscription::class, 'subscribable')
            ->whereIn('status', ['active', 'trialing'])
            ->latestOfMany();
    }

    /**
     * Check if a feature is granted via the shop's plan or a direct grant.
     *
     * Delegates to Subscriptionify's own resolution (plan features +
     * per-shop direct grants). The alias is required because this method
     * overrides the trait's `hasFeature` under the same name.
     */
    public function hasFeature(string $key): bool
    {
        return $this->subscriptionifyHasFeature($key);
    }

    public function grantFeature(
        string $slug,
        ?int $value = null,
        ?string $unitPrice = null,
        ?int $resetPeriod = null,
        ?Interval $resetInterval = null,
    ): void {
        $this->unsetRelation('directFeatures');
        $this->featureGrantService()->grant(
            $this, $slug, $value, $unitPrice, $resetPeriod, $resetInterval,
        );
        $this->unsetRelation('directFeatures');
    }

    public function revokeFeature(string $slug): void
    {
        $this->unsetRelation('directFeatures');
        $this->featureGrantService()->revoke($this, $slug);
        $this->unsetRelation('directFeatures');
    }

    /**
     * Generate next sequential store code (e.g. shop-001, shop-002).
     */
    public static function generateNextStoreCode(string $prefix = 'shop-'): string
    {
        $codes = static::query()
            ->whereNotNull('store_code')
            ->where(function ($query) use ($prefix) {
                $query->where('store_code', 'LIKE', strtolower($prefix).'%')
                    ->orWhere('store_code', 'LIKE', strtoupper($prefix).'%');
            })
            ->pluck('store_code');

        $maxNumber = 0;
        foreach ($codes as $code) {
            $numberStr = substr($code, strlen($prefix));
            if (is_numeric($numberStr)) {
                $num = (int) $numberStr;
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        $nextNumber = $maxNumber + 1;
        $code = sprintf('%s%03d', $prefix, $nextNumber);

        while (static::where('store_code', $code)->exists()) {
            $nextNumber++;
            $code = sprintf('%s%03d', $prefix, $nextNumber);
        }

        return $code;
    }
}
