<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Employee\Models\Employee;
use Modules\Shop\Models\Shop;

/**
 * An employee's pay for a payroll run, with its earning and deduction lines.
 */
class Payslip extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'payroll_run_id', 'company_id', 'shop_id', 'employee_id', 'employee_code', 'employee_name', 'designation', 'salary', 'basic',
        'days_in_month', 'payable_days', 'present_days', 'absent_days', 'paid_leave_days', 'unpaid_leave_days', 'late_days',
        'overtime_minutes', 'other_addition', 'other_deduction', 'adjustment_note', 'pf_employee', 'pf_employer', 'tax', 'taxable_income',
        'earnings_total', 'deductions_total', 'net_pay', 'paid_amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'salary' => 'decimal:2',
            'present_days' => 'float',
            'absent_days' => 'float',
            'paid_leave_days' => 'float',
            'unpaid_leave_days' => 'float',
            'late_days' => 'integer',
            'overtime_minutes' => 'integer',
            'basic' => 'decimal:2',
            'other_addition' => 'decimal:2',
            'other_deduction' => 'decimal:2',
            'pf_employee' => 'decimal:2',
            'pf_employer' => 'decimal:2',
            'tax' => 'decimal:2',
            'taxable_income' => 'decimal:2',
            'earnings_total' => 'decimal:2',
            'deductions_total' => 'decimal:2',
            'net_pay' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayslipItem::class)->orderBy('sort_order');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(PayrollPayment::class, 'payable');
    }

    public function due(): float
    {
        return round((float) $this->net_pay - (float) $this->paid_amount, 2);
    }
}
