<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Concerns\BelongsToCompany;

/**
 * A part of the salary structure. Earnings calculated as a percentage of
 * gross split the gross salary (basic, house rent, medical, conveyance);
 * fixed or basic-based earnings and deductions come on top of it.
 */
class SalaryComponent extends Model
{
    use BelongsToCompany;

    public const CALCULATIONS = ['percent_of_gross', 'percent_of_basic', 'fixed'];

    protected $fillable = ['company_id', 'name', 'code', 'type', 'calculation', 'value', 'is_basic', 'is_taxable', 'is_active', 'sort_order'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['value' => 'float', 'is_basic' => 'boolean', 'is_taxable' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function splitsGross(): bool
    {
        return $this->type === 'earning' && $this->calculation === 'percent_of_gross';
    }
}
