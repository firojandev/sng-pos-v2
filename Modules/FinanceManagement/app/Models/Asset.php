<?php

namespace Modules\FinanceManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Core\Observers\AuditObserver;
use Modules\Core\Support\BanglaNumber;

class Asset extends Model
{
    use BelongsToShop, SoftDeletes;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $attributes = [
        'depreciation_type' => 'flat',
        'depreciation' => 0,
        'validity_unit' => 'year',
    ];

    protected $fillable = [
        'shop_id',
        'name',
        'amount',
        'purchase_date',
        'residual_value',
        'depreciation_type',
        'depreciation',
        'validity',
        'validity_unit',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'purchase_date' => 'date',
        'residual_value' => 'decimal:2',
        'depreciation' => 'decimal:2',
        'validity' => 'decimal:2',
    ];

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class)->orderBy('period');
    }

    public function isStraightLine(): bool
    {
        return $this->depreciation_type === 'straight_line';
    }

    /**
     * The useful life (the validity) in months.
     */
    public function usefulLifeMonths(): int
    {
        $validity = (float) $this->validity;

        return (int) round(match ($this->validity_unit) {
            'month' => $validity,
            'day' => $validity / 30,
            default => $validity * 12,
        });
    }

    /**
     * Calculate the monetary depreciation amount based on type.
     */
    public function getDepreciationAmountAttribute(): float
    {
        if ($this->isStraightLine()) {
            return round((float) $this->depreciations()->sum('amount'), 2);
        }

        $dep = (float) ($this->depreciation ?? 0);
        if ($this->depreciation_type === 'percentage') {
            return round(((float) $this->amount * $dep) / 100, 2);
        }

        return $dep;
    }

    /**
     * Net book value after subtracting depreciation.
     */
    public function getNetValueAttribute(): float
    {
        return max(0, (float) $this->amount - $this->depreciation_amount);
    }

    /**
     * Human-readable validity representation with bilingual units.
     */
    public function getValidityFormattedAttribute(): ?string
    {
        if ($this->validity === null || $this->validity === '') {
            return null;
        }

        $val = (float) $this->validity;
        $valStr = ($val == (int) $val) ? (string) (int) $val : (string) $val;
        $valBn = BanglaNumber::toBn($valStr);

        $units = [
            'day' => ['bn' => 'দিন', 'en' => $val == 1 ? 'Day' : 'Days'],
            'month' => ['bn' => 'মাস', 'en' => $val == 1 ? 'Month' : 'Months'],
            'year' => ['bn' => 'বছর', 'en' => $val == 1 ? 'Year' : 'Years'],
        ];

        $unit = $units[$this->validity_unit] ?? $units['year'];

        return '<span class="bn">'.$valBn.' '.$unit['bn'].'</span><span class="en" style="display:none;">'.$valStr.' '.$unit['en'].'</span>';
    }

    public function getUsefulLifeAttribute()
    {
        return $this->validity;
    }

    public function getUsefulLifeUnitAttribute()
    {
        return $this->validity_unit;
    }

    public function getUsefulLifeFormattedAttribute(): ?string
    {
        return $this->validity_formatted;
    }
}
