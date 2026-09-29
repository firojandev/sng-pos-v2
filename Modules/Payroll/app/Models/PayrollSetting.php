<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Concerns\BelongsToCompany;

/**
 * A company's payroll rules. The defaults follow the Bangladesh Labour Act
 * 2006: overtime at twice the hourly basic (basic ÷ 208), absence deducted
 * at a day's basic (basic ÷ 30), one basic as each festival bonus.
 *
 * salary_change_policy: a salary change inside a month is paid
 * - prorated: from its effective day, by days;
 * - next_month: from the next month (a change on the 1st counts at once);
 * - full_month: for the whole month it takes effect in.
 */
class PayrollSetting extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'overtime_enabled', 'overtime_multiplier', 'overtime_hours_base', 'attendance_deductions', 'salary_change_policy',
        'absence_deduction_basis', 'late_days_per_deduction', 'pf_enabled', 'pf_employee_percent', 'pf_employer_percent',
        'pf_eligible_after_months', 'pf_employer_vesting_years', 'tax_enabled', 'default_tax_location', 'festival_bonus_percent', 'festival_bonus_min_months',
        'festival_bonuses_per_year',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'overtime_enabled' => 'boolean',
            'overtime_multiplier' => 'float',
            'overtime_hours_base' => 'integer',
            'attendance_deductions' => 'boolean',
            'late_days_per_deduction' => 'integer',
            'pf_enabled' => 'boolean',
            'pf_employee_percent' => 'float',
            'pf_employer_percent' => 'float',
            'pf_eligible_after_months' => 'integer',
            'pf_employer_vesting_years' => 'integer',
            'tax_enabled' => 'boolean',
            'festival_bonus_percent' => 'float',
            'festival_bonus_min_months' => 'integer',
            'festival_bonuses_per_year' => 'integer',
        ];
    }

    public const SALARY_CHANGE_POLICIES = ['prorated', 'next_month', 'full_month'];

    public static function forCompany(int $companyId): self
    {
        return static::withoutGlobalScopes()->firstOrCreate(['company_id' => $companyId])->refresh();
    }
}
