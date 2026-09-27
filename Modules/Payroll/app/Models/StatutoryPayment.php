<?php

namespace Modules\Payroll\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Core\Observers\AuditObserver;
use Modules\Finance\Models\Account;

/**
 * Provident fund (employee and employer shares) or income tax withheld by
 * payroll, paid over to the fund or the government for a period.
 */
class StatutoryPayment extends Model
{
    use BelongsToCompany;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = [
        'company_id', 'type', 'period_from', 'period_to', 'employee_amount', 'employer_amount', 'amount', 'payee', 'account_id',
        'shop_id', 'payment_date', 'reference', 'status', 'note', 'created_by', 'paid_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_from' => 'date',
            'period_to' => 'date',
            'payment_date' => 'date',
            'employee_amount' => 'decimal:2',
            'employer_amount' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
