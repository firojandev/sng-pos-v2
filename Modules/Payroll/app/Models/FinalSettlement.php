<?php

namespace Modules\Payroll\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Core\Observers\AuditObserver;
use Modules\Employee\Models\Employee;
use Modules\Shop\Models\Shop;

/**
 * What is owed to (or by) an employee who leaves: gratuity or compensation,
 * notice pay, unused earned leave, their provident fund, less loans.
 */
class FinalSettlement extends Model
{
    use BelongsToCompany;

    public const SEPARATION_TYPES = ['resignation', 'termination', 'dismissal', 'retirement', 'death', 'contract_end'];

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = [
        'company_id', 'shop_id', 'employee_id', 'separation_type', 'separation_date', 'service_days', 'last_salary', 'last_basic',
        'pf_forfeited', 'earnings_total', 'deductions_total', 'net_pay', 'paid_amount', 'status', 'note', 'finalized_by',
        'finalized_at', 'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'separation_date' => 'date',
            'finalized_at' => 'datetime',
            'last_salary' => 'decimal:2',
            'last_basic' => 'decimal:2',
            'pf_forfeited' => 'decimal:2',
            'earnings_total' => 'decimal:2',
            'deductions_total' => 'decimal:2',
            'net_pay' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    /**
     * @return array<string, array{bn: string, en: string}>
     */
    public static function separationLabels(): array
    {
        return [
            'resignation' => ['bn' => 'পদত্যাগ', 'en' => 'Resignation'],
            'termination' => ['bn' => 'অবসান (টার্মিনেশন)', 'en' => 'Termination'],
            'dismissal' => ['bn' => 'বরখাস্ত (অসদাচরণ)', 'en' => 'Dismissal (Misconduct)'],
            'retirement' => ['bn' => 'অবসর', 'en' => 'Retirement'],
            'death' => ['bn' => 'মৃত্যু', 'en' => 'Death'],
            'contract_end' => ['bn' => 'চুক্তির মেয়াদ শেষ', 'en' => 'Contract End'],
        ];
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
        return $this->hasMany(FinalSettlementItem::class)->orderBy('sort_order');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(PayrollPayment::class, 'payable');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function due(): float
    {
        return round((float) $this->net_pay - (float) $this->paid_amount, 2);
    }
}
