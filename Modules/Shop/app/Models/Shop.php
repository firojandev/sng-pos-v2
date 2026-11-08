<?php

namespace Modules\Shop\Models;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Modules\Company\Models\Company;
use Modules\Finance\Models\Account;
use Modules\Product\Models\Category;
use Revoltify\Subscriptionify\DTOs\FeatureInfo;
use Revoltify\Subscriptionify\DTOs\SubscriptionInfo;
use Revoltify\Subscriptionify\Enums\Interval;
use Revoltify\Subscriptionify\Models\Contracts\HasPlan;
use Revoltify\Subscriptionify\Models\Contracts\HasSubscription;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * A shop always belongs to a company. Billing lives on the company, so the
 * subscription and plan-feature methods below forward to it; existing
 * `$shop->hasFeature()` / `$shop->subscription()` callers keep working.
 */
class Shop extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'store_code',
        'phone',
        'email',
        'address',
        'logo',
        'invoice_footer',
        'currency_symbol',
        'status',
        'business_type',
        'loyalty_enabled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['loyalty_enabled' => 'boolean'];
    }

    /**
     * Get the shop's logo URL.
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (! $this->logo) {
                    return null;
                }

                if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
                    return $this->logo;
                }

                $clean = ltrim($this->logo, '/');
                $path = str_starts_with($clean, 'storage/') ? $clean : 'storage/'.$clean;

                return asset($path);
            }
        );
    }

    protected static function booted(): void
    {
        static::creating(function (Shop $shop) {
            if (empty($shop->company_id)) {
                $shop->company_id = Company::createForShop($shop)->id;
            }
        });

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
     * Printer configuration for this shop.
     */
    public function printerSetting(): HasOne
    {
        return $this->hasOne(PrinterSetting::class, 'shop_id');
    }

    /**
     * Get the effective printer setting for the shop.
     */
    public function getEffectivePrinterSetting(): PrinterSetting
    {
        $setting = $this->printerSetting ?? PrinterSetting::getDefaultForShop($this->id);
        $setting->setRelation('shop', $this);

        return $setting;
    }

    /**
     * Branches belonging to this shop.
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class, 'shop_id');
    }

    /**
     * Warehouses belonging to this shop.
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'shop_id');
    }

    /**
     * The product categories this shop sells.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'shop_category')->withTimestamps();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Active subscription of the shop's company. A real relation (through the
     * company) so it can be eager loaded, e.g. `Shop::with('activeSubscription')`.
     */
    public function activeSubscription(): HasOneThrough
    {
        return $this->hasOneThrough(Subscription::class, Company::class, 'id', 'subscribable_id', 'company_id', 'id')
            ->where('subscriptions.subscribable_type', Company::class)
            ->whereIn('subscriptions.status', ['active', 'trialing'])
            ->latest('subscriptions.id');
    }

    public function subscriptions(): MorphMany
    {
        return $this->billingCompany()->subscriptions();
    }

    public function featureUsages(): MorphMany
    {
        return $this->billingCompany()->featureUsages();
    }

    public function directFeatures(): MorphToMany
    {
        return $this->billingCompany()->directFeatures();
    }

    public function subscribe(HasPlan $plan, ?CarbonInterface $endsAt = null): HasSubscription
    {
        return $this->billingCompany()->subscribe($plan, $endsAt);
    }

    public function onPlan(HasPlan $plan): bool
    {
        return $this->billingCompany()->onPlan($plan);
    }

    public function canChangePlan(HasPlan $plan): bool
    {
        return $this->billingCompany()->canChangePlan($plan);
    }

    public function onFreePlan(): bool
    {
        return $this->billingCompany()->onFreePlan();
    }

    public function subscription(): ?HasSubscription
    {
        return $this->billingCompany()->subscription();
    }

    public function billingSubscription(): ?Subscription
    {
        return $this->billingCompany()->billingSubscription();
    }

    public function clearSubscriptionCache(): static
    {
        $this->billingCompany()->clearSubscriptionCache();

        return $this;
    }

    public function subscribed(): bool
    {
        return $this->billingCompany()->subscribed();
    }

    public function onTrial(): bool
    {
        return $this->billingCompany()->onTrial();
    }

    public function subscriptionInfo(): SubscriptionInfo
    {
        return $this->billingCompany()->subscriptionInfo();
    }

    /**
     * Check if a feature is granted via the company's plan or a direct grant.
     */
    public function hasFeature(string $key): bool
    {
        return $this->billingCompany()->hasFeature($key);
    }

    public function canConsume(string $slug, int|float|string $units = 1): bool
    {
        return $this->billingCompany()->canConsume($slug, $units);
    }

    public function consume(string $slug, int|float|string $units = 1): void
    {
        $this->billingCompany()->consume($slug, $units);
    }

    public function tryConsume(string $slug, int|float|string $units = 1): bool
    {
        return $this->billingCompany()->tryConsume($slug, $units);
    }

    public function release(string $slug, int|float|string $units = 1): void
    {
        $this->billingCompany()->release($slug, $units);
    }

    public function remainingUsage(string $slug): string
    {
        return $this->billingCompany()->remainingUsage($slug);
    }

    public function featureInfo(string $slug): FeatureInfo
    {
        return $this->billingCompany()->featureInfo($slug);
    }

    public function isUnlimitedUsage(string $slug): bool
    {
        return $this->billingCompany()->isUnlimitedUsage($slug);
    }

    public function allFeatures(): Collection
    {
        return $this->billingCompany()->allFeatures();
    }

    public function grantFeature(
        string $slug,
        ?int $value = null,
        ?string $unitPrice = null,
        ?int $resetPeriod = null,
        ?Interval $resetInterval = null,
    ): void {
        $company = $this->billingCompany();
        $company->unsetRelation('directFeatures');
        $company->grantFeature($slug, $value, $unitPrice, $resetPeriod, $resetInterval);
        $company->unsetRelation('directFeatures');
    }

    public function revokeFeature(string $slug): void
    {
        $company = $this->billingCompany();
        $company->unsetRelation('directFeatures');
        $company->revokeFeature($slug);
        $company->unsetRelation('directFeatures');
    }

    /**
     * The company that holds this shop's subscription.
     */
    public function billingCompany(): Company
    {
        if (! $this->company && $this->exists && ! $this->company_id) {
            // The shop was loaded without its company_id column.
            $this->company_id = static::query()->whereKey($this->getKey())->value('company_id');
            $this->unsetRelation('company');
        }

        return $this->company;
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
