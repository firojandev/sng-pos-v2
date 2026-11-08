<?php

namespace Modules\Payroll\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Core\Observers\AuditObserver;
use Modules\Shop\Models\Shop;

/**
 * One month's salary (or a festival bonus) for the staff of a shop. A draft
 * can be recalculated and adjusted; approving it posts it to the ledger and
 * makes the payslips payable.
 */
class PayrollRun extends Model
{
    use BelongsToCompany;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = [
        'company_id', 'shop_id', 'type', 'title', 'month', 'pay_date', 'status', 'employees_count', 'earnings_total',
        'deductions_total', 'net_total', 'paid_total', 'note', 'approved_by', 'approved_at', 'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'month' => 'date',
            'pay_date' => 'date',
            'approved_at' => 'datetime',
            'earnings_total' => 'decimal:2',
            'deductions_total' => 'decimal:2',
            'net_total' => 'decimal:2',
            'paid_total' => 'decimal:2',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isBonus(): bool
    {
        return $this->type === 'bonus';
    }

    public function label(): string
    {
        return $this->isBonus() ? ($this->title ?: 'Festival Bonus').' — '.$this->month->format('M Y') : $this->month->format('F Y');
    }

    public function paymentStatus(): string
    {
        if ($this->isDraft()) {
            return 'draft';
        }

        return match (true) {
            (float) $this->paid_total <= 0 => 'unpaid',
            (float) $this->paid_total + 0.005 < (float) $this->net_total => 'partial',
            default => 'paid',
        };
    }

    public function refreshTotals(): void
    {
        $totals = $this->payslips()->selectRaw('COUNT(*) as employees, SUM(earnings_total) as earnings, SUM(deductions_total) as deductions, SUM(net_pay) as net, SUM(paid_amount) as paid')->first();

        $this->forceFill([
            'employees_count' => (int) $totals->employees,
            'earnings_total' => round((float) $totals->earnings, 2),
            'deductions_total' => round((float) $totals->deductions, 2),
            'net_total' => round((float) $totals->net, 2),
            'paid_total' => round((float) $totals->paid, 2),
        ])->saveQuietly();
    }
}
