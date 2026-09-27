<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Company\Models\Company;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Core\Observers\AuditObserver;
use Modules\Core\Support\TenantContext;
use Modules\Sales\Models\Sale;

/**
 * Shared by every shop of the company; shop_id records the shop that created it.
 */
class Customer extends Model
{
    use BelongsToCompany;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);

        static::creating(function (Customer $model) {
            if (empty($model->shop_id)) {
                $model->shop_id = app(TenantContext::class)->shopId();
            }
        });
    }

    protected $fillable = ['company_id', 'shop_id', 'name', 'phone', 'email', 'address', 'opening_due', 'status'];

    protected $casts = [
        'opening_due' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Records created at the given shop. A shop's own financial figures count
     * the opening dues of these, so shop totals add up to the company total.
     */
    #[Scope]
    protected function createdAtShop(Builder $query, ?int $shopId): void
    {
        $query->where($query->getModel()->getTable().'.shop_id', $shopId);
    }

    public function setOpeningDueAttribute($value): void
    {
        $this->attributes['opening_due'] = ($value === null || $value === '') ? 0 : $value;
    }

    /**
     * All of the sales made across the company's shops. Dues are company-wide:
     * any shop can see and settle them.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class)->withoutGlobalScope('shop');
    }

    public function membership(): HasOne
    {
        return $this->hasOne(CustomerMembership::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyPointTransaction::class);
    }

    public function isLoyaltyMember(): bool
    {
        return (bool) $this->membership?->isActive();
    }

    public function loyaltyPoints(): int
    {
        return (int) $this->pointTransactions()->sum('points');
    }

    public function totalDue(): string
    {
        return bcadd((string) $this->opening_due, (string) $this->sales()->sum('due_amount'), 2);
    }
}
