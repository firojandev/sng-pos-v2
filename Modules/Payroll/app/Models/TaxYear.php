<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Concerns\BelongsToCompany;

/**
 * An income year's (July–June) individual income tax rules: tax-free
 * thresholds per category, the salary exemption (a share of income,
 * capped), the slabs ([width, rate %], the last one without a width), the
 * investment rebate rules and the minimum tax per location.
 */
class TaxYear extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'assessment_year', 'starts_on', 'ends_on', 'thresholds', 'exemption_percent', 'exemption_cap',
        'minimum_taxes', 'rebate_income_percent', 'rebate_investment_percent', 'rebate_cap', 'slabs',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'thresholds' => 'array',
            'exemption_percent' => 'float',
            'exemption_cap' => 'float',
            'minimum_taxes' => 'array',
            'rebate_income_percent' => 'float',
            'rebate_investment_percent' => 'float',
            'rebate_cap' => 'float',
            'slabs' => 'array',
        ];
    }

    public function threshold(string $category): float
    {
        return (float) ($this->thresholds[$category] ?? $this->thresholds['general'] ?? 0);
    }

    public function minimumTax(string $location): float
    {
        return (float) ($this->minimum_taxes[$location] ?? max($this->minimum_taxes ?: [0]));
    }
}
