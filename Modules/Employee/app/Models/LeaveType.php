<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Concerns\BelongsToCompany;

/**
 * A kind of leave. Either a yearly allowance (days_per_year) or earned
 * leave accrued at one day per `earn_one_day_per` days worked.
 *
 * Unused days can be carried into the next year (up to max_carry_forward,
 * none = no limit), optionally expiring carry_forward_expiry_months into
 * that year, and paid out on leaving when is_encashable.
 */
class LeaveType extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'code', 'days_per_year', 'is_paid', 'earn_one_day_per',
        'carry_forward', 'max_carry_forward', 'carry_forward_expires', 'carry_forward_expiry_months', 'is_encashable', 'gender', 'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'days_per_year' => 'integer',
            'earn_one_day_per' => 'integer',
            'is_paid' => 'boolean',
            'carry_forward' => 'boolean',
            'max_carry_forward' => 'integer',
            'carry_forward_expires' => 'boolean',
            'carry_forward_expiry_months' => 'integer',
            'is_encashable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function isEarned(): bool
    {
        return (int) $this->earn_one_day_per > 0;
    }
}
