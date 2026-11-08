<?php

namespace Modules\Payroll\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Employee\Models\Employee;
use Modules\Finance\Models\Account;

/**
 * Salary (or a final settlement) paid to an employee from a money account.
 */
class PayrollPayment extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'shop_id', 'payable_type', 'payable_id', 'employee_id', 'account_id', 'amount', 'paid_on', 'note', 'created_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_on' => 'date'];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ledgerParty(): ?Employee
    {
        return Employee::withoutGlobalScopes()->find($this->employee_id);
    }
}
