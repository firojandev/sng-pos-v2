<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Company\Models\Company;

/**
 * A company's loyalty programme: every spend_amount spent earns
 * points_per_spend points; a point is worth point_value taka when redeemed.
 */
class LoyaltyProgram extends Model
{
    protected $fillable = [
        'company_id',
        'is_enabled',
        'spend_amount',
        'points_per_spend',
        'point_value',
        'min_redeem_points',
        'max_redeem_percent',
        'points_expire_after_days',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'spend_amount' => 'decimal:2',
            'points_per_spend' => 'integer',
            'point_value' => 'decimal:2',
            'min_redeem_points' => 'integer',
            'max_redeem_percent' => 'integer',
            'points_expire_after_days' => 'integer',
        ];
    }

    public static function forCompany(?int $companyId): self
    {
        return static::firstOrNew(['company_id' => $companyId]);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Points earned for spending the given amount.
     */
    public function pointsFor(float $amount): int
    {
        if ((float) $this->spend_amount <= 0) {
            return 0;
        }

        return (int) floor(max($amount, 0) / (float) $this->spend_amount) * (int) $this->points_per_spend;
    }
}
