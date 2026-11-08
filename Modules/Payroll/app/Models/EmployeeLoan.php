<?php

namespace Modules\Payroll\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Core\Observers\AuditObserver;
use Modules\Employee\Models\Employee;
use Modules\Finance\Models\Account;
use Modules\Shop\Models\Shop;

/**
 * Money advanced or lent to an employee, recovered in monthly installments
 * from the salary (or repaid in cash, or out of the final settlement).
 */
class EmployeeLoan extends Model
{
    use BelongsToCompany;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = ['company_id', 'shop_id', 'employee_id', 'type', 'amount', 'installment', 'issued_on', 'deduct_from', 'account_id', 'status', 'note', 'created_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'installment' => 'decimal:2', 'issued_on' => 'date', 'deduct_from' => 'date'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recoveries(): HasMany
    {
        return $this->hasMany(EmployeeLoanRecovery::class);
    }

    public function recovered(): float
    {
        return round((float) $this->recoveries()->sum('amount'), 2);
    }

    public function balance(): float
    {
        return round((float) $this->amount - $this->recovered(), 2);
    }

    /**
     * The party the ledger records this money movement against.
     */
    public function ledgerParty(): ?Employee
    {
        return Employee::withoutGlobalScopes()->find($this->employee_id);
    }
}
