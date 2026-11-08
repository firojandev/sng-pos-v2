<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Shop\Models\Shop;

/**
 * One debit or credit of a journal entry, tagged with the shop it belongs
 * to and optionally a party (customer, supplier, employee).
 */
class JournalLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'journal_entry_id', 'company_id', 'ledger_account_id', 'shop_id',
        'debit', 'credit', 'party_type', 'party_id', 'memo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['debit' => 'decimal:2', 'credit' => 'decimal:2'];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_account_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function party(): MorphTo
    {
        return $this->morphTo();
    }
}
