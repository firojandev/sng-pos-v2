<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToCompany;

/**
 * An account in the company's chart of accounts. Group accounts organise
 * the tree; entries are posted only to non-group accounts.
 */
class LedgerAccount extends Model
{
    use BelongsToCompany;

    public const TYPES = ['asset', 'liability', 'equity', 'income', 'expense'];

    protected $fillable = [
        'company_id', 'parent_id', 'code', 'name', 'type', 'is_group',
        'system_key', 'allow_manual_posting', 'is_active', 'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_group' => 'boolean',
            'allow_manual_posting' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('code');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     * Assets and expenses grow with debits; the rest grow with credits.
     */
    public function isDebitNormal(): bool
    {
        return in_array($this->type, ['asset', 'expense'], true);
    }

    /**
     * Balance in the account's normal direction.
     */
    public function normalBalance(float $debit, float $credit): float
    {
        return round($this->isDebitNormal() ? $debit - $credit : $credit - $debit, 2);
    }
}
