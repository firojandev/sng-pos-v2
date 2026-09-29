<?php

namespace Modules\Accounting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Shop\Models\Shop;

/**
 * A balanced journal entry: its lines' debits equal their credits.
 * Entries are never edited; a mistake is corrected by reversing it.
 */
class JournalEntry extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'shop_id', 'number', 'entry_date', 'narration', 'reference',
        'source_type', 'source_id', 'reversal_of_id', 'reversed_at', 'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['entry_date' => 'date', 'reversed_at' => 'datetime'];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class)->orderBy('id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    public function reversal(): HasOne
    {
        return $this->hasOne(self::class, 'reversal_of_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isReversed(): bool
    {
        return $this->reversed_at !== null;
    }

    public function total(): float
    {
        return round((float) $this->lines->sum('debit'), 2);
    }
}
