<?php

namespace Modules\Company\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Company\Database\Factories\CompanyFactory;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Subscription;
use Revoltify\Subscriptionify\Concerns\InteractsWithSubscriptions;
use Revoltify\Subscriptionify\Contracts\Subscribable;

/**
 * The business entity that owns one or more shops. Billing (subscription
 * and plan features) belongs to the company, and company-wide ERP data
 * (HR, payroll, accounting, customers, suppliers) is scoped to it.
 *
 * Types: a "company" (its plan covers all its shops); the "default" company
 * grouping every standalone shop (no plan of its own); and a "standalone"
 * company, the private record behind one standalone shop under the default
 * company, holding that shop's own plan and keeping its data apart.
 *
 * Every shop has a company. The owner and admins of a "company" run it: its
 * details, its shops and their admins.
 */
class Company extends Model implements Subscribable
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    use InteractsWithSubscriptions;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'parent_id',
        'legal_name',
        'trade_license_no',
        'tin',
        'bin',
        'phone',
        'email',
        'address',
        'logo',
        'fiscal_year_start_month',
        'currency',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fiscal_year_start_month' => 'integer',
        ];
    }

    protected static function newFactory(): CompanyFactory
    {
        return CompanyFactory::new();
    }

    public const TYPE_COMPANY = 'company';

    public const TYPE_DEFAULT = 'default';

    public const TYPE_STANDALONE = 'standalone';

    /**
     * The Default Company that groups every standalone shop (created the
     * first time it's needed; id 1 on a fresh install).
     */
    public static function defaultCompany(): self
    {
        return static::withTrashed()->where('type', self::TYPE_DEFAULT)->oldest('id')->first()
            ?? static::create([
                'name' => 'ডিফল্ট কোম্পানি (Default Company)',
                'slug' => static::generateUniqueSlug('default-company'),
                'type' => self::TYPE_DEFAULT,
                'status' => 'active',
            ]);
    }

    public function isDefault(): bool
    {
        return $this->type === self::TYPE_DEFAULT;
    }

    public function isStandalone(): bool
    {
        return $this->type === self::TYPE_STANDALONE;
    }

    /**
     * A real company (not the default grouping, not a standalone shop).
     */
    public function isBusiness(): bool
    {
        return ($this->type ?? self::TYPE_COMPANY) === self::TYPE_COMPANY;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * The name shown for the company a shop belongs to: a standalone shop
     * shows the Default Company.
     */
    public function displayName(): string
    {
        return $this->isStandalone() ? ($this->parent?->name ?? static::defaultCompany()->name) : $this->name;
    }

    /**
     * The standalone shops grouped under this (default) company.
     *
     * @return Builder<Shop>
     */
    public function standaloneShops(): Builder
    {
        return Shop::query()->whereHas('company', fn ($company) => $company->where('type', self::TYPE_STANDALONE)->where('parent_id', $this->id));
    }

    /**
     * Create the private company for a shop that is being created
     * without one (a standalone shop under the Default Company), copying the
     * shop's identity details.
     */
    public static function createForShop(Shop $shop): self
    {
        return static::create([
            'name' => $shop->name,
            'slug' => static::generateUniqueSlug((string) ($shop->slug ?: $shop->name)),
            'type' => self::TYPE_STANDALONE,
            'parent_id' => static::defaultCompany()->id,
            'phone' => $shop->phone,
            'email' => $shop->email,
            'address' => $shop->address,
            'logo' => $shop->logo,
            'status' => $shop->status === 'inactive' ? 'inactive' : 'active',
        ]);
    }

    public static function generateUniqueSlug(string $base): string
    {
        $base = Str::slug($base) ?: 'company';
        $slug = $base;
        $suffix = 2;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * Get the company's logo URL.
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

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    /**
     * Users who belong to the company (owners and company-level staff).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withPivot('role', 'is_owner', 'company_role_id')
            ->withTimestamps();
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
     * The subscription that decides whether the company's shops may use the
     * app: the active/trialing one when there is one, otherwise the most
     * recent subscription in any status (e.g. suspended or cancelled), so
     * that a suspension actually blocks access.
     */
    public function billingSubscription(): ?Subscription
    {
        return $this->subscription() ?? $this->subscriptions()->latest('id')->first();
    }

    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('is_owner', true);
    }

    /**
     * Company screens and labels are only shown once a company runs more
     * than one shop; a single-shop company is presented as "just a shop".
     */
    /**
     * Company-level roles: the owner, company admins and company employees
     * log in to the company workspace (no shop, no POS). "Member" marks a
     * shop user linked to the company (not a company-level login).
     */
    public const ROLE_OWNER = 'Owner';

    public const ROLE_ADMIN = 'Admin';

    public const ROLE_EMPLOYEE = 'Employee';

    public const ROLE_MEMBER = 'Member';

    /**
     * @var list<string>
     */
    public const COMPANY_LEVEL_ROLES = [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_EMPLOYEE];

    /**
     * The company's company-level users (owner, admins, employees).
     */
    public function companyUsers(): BelongsToMany
    {
        return $this->users()->where(fn ($query) => $query->where('company_user.is_owner', true)->orWhereIn('company_user.role', self::COMPANY_LEVEL_ROLES));
    }

    /**
     * Whether the user runs this company (its owner or a company admin).
     */
    public function isAdministeredBy(User $user): bool
    {
        return $this->users()
            ->where('users.id', $user->id)
            ->where(fn ($query) => $query->where('company_user.is_owner', true)->orWhereIn('company_user.role', [self::ROLE_OWNER, self::ROLE_ADMIN]))
            ->exists();
    }

    public function roles(): HasMany
    {
        return $this->hasMany(CompanyRole::class);
    }

    /**
     * The company role of one of its employees (null for admins or none).
     */
    public function roleOf(User $user): ?CompanyRole
    {
        $roleId = $this->users()->where('users.id', $user->id)->value('company_user.company_role_id');

        return $roleId ? CompanyRole::find($roleId) : null;
    }

    public function hasOwner(): bool
    {
        return $this->users()->wherePivot('is_owner', true)->exists();
    }

    public function owner(): ?User
    {
        return $this->users()->wherePivot('is_owner', true)->oldest('company_user.id')->first();
    }

    public function hasMultipleShops(): bool
    {
        return $this->shops()->count() > 1;
    }
}
