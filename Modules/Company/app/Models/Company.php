<?php

namespace Modules\Company\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
 * Every shop has a company. A company with a single shop is created
 * automatically and stays hidden from the shop owner.
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

    /**
     * Create the hidden single-shop company for a shop that is being created
     * without one, copying the shop's identity details.
     */
    public static function createForShop(Shop $shop): self
    {
        return static::create([
            'name' => $shop->name,
            'slug' => static::generateUniqueSlug((string) ($shop->slug ?: $shop->name)),
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
            ->withPivot('role', 'is_owner')
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
    public function hasMultipleShops(): bool
    {
        return $this->shops()->count() > 1;
    }
}
