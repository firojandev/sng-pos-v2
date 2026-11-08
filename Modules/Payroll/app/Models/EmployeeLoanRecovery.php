<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Employee\Models\Employee;

/**
 * Part of a loan paid back: a payroll deduction, a settlement deduction or
 * a cash repayment (with the money account it went into).
 */
class EmployeeLoanRecovery extends Model
{
    protected $fillable = ['employee_loan_id', 'amount', 'recovered_on', 'source_type', 'source_id', 'account_id', 'note', 'created_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'recovered_on' => 'date'];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(EmployeeLoan::class, 'employee_loan_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function ledgerParty(): ?Employee
    {
        $employeeId = EmployeeLoan::withoutGlobalScopes()->whereKey($this->employee_loan_id)->value('employee_id');

        return $employeeId ? Employee::withoutGlobalScopes()->find($employeeId) : null;
    }
}
