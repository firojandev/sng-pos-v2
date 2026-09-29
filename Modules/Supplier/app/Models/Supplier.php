<?php

namespace Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Company\Models\Company;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Core\Observers\AuditObserver;
use Modules\Core\Support\TenantContext;
use Modules\Purchase\Models\Purchase;

/**
 * Shared by every shop of the company; shop_id records the shop that created it.
 */
class Supplier extends Model
{
    use BelongsToCompany;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);

        static::creating(function (Supplier $model) {
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
     * All of the purchases made across the company's shops. Dues are company-wide:
     * any shop can see and settle them.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class)->withoutGlobalScope('shop');
    }

    public function totalDue(): string
    {
        return bcadd((string) $this->opening_due, (string) $this->purchases()->sum('due_amount'), 2);
    }
}
