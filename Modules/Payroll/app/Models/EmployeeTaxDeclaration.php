<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Employee\Models\Employee;

/**
 * An employee's income tax for one income year: the investments they
 * declare (and how much qualifies), the rebate, the tax deducted and, once
 * the year is closed, what is still owed (+) or to be refunded (−).
 */
class EmployeeTaxDeclaration extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'employee_id', 'tax_year_id', 'declared_investment', 'eligible_investment', 'calculated_rebate',
        'taxable_income', 'annual_tax', 'tax_deducted', 'year_end_adjustment', 'note', 'closed_at', 'closed_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'declared_investment' => 'decimal:2',
            'eligible_investment' => 'decimal:2',
            'calculated_rebate' => 'decimal:2',
            'taxable_income' => 'decimal:2',
            'annual_tax' => 'decimal:2',
            'tax_deducted' => 'decimal:2',
            'year_end_adjustment' => 'decimal:2',
            'closed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function taxYear(): BelongsTo
    {
        return $this->belongsTo(TaxYear::class);
    }
}
