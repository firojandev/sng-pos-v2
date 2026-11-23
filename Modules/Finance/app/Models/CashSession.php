<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Core\Observers\AuditObserver;

/**
 * A cash drawer session on one cash account, from opening to closing count.
 */
class CashSession extends Model
{
    use BelongsToShop;

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = [
        'shop_id', 'account_id', 'status',
        'opened_by', 'opened_at', 'system_balance_at_open', 'expected_opening_balance',
        'counted_opening_balance', 'opening_balance', 'opening_difference', 'opening_note',
        'closed_by', 'closed_at', 'system_balance_at_close', 'expected_closing_balance',
        'counted_closing_balance', 'closing_difference', 'closing_note', 'movement_summary',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'system_balance_at_open' => 'decimal:2',
            'expected_opening_balance' => 'decimal:2',
            'counted_opening_balance' => 'decimal:2',
            'opening_balance' => 'decimal:2',
            'opening_difference' => 'decimal:2',
            'system_balance_at_close' => 'decimal:2',
            'expected_closing_balance' => 'decimal:2',
            'counted_closing_balance' => 'decimal:2',
            'closing_difference' => 'decimal:2',
            'movement_summary' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)->withTrashed();
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function counts(): HasMany
    {
        return $this->hasMany(CashSessionCount::class)->orderByDesc('denomination');
    }

    /**
     * @param  Builder<CashSession>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', self::STATUS_OPEN);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Over / short / balanced label for a cash difference.
     *
     * @return array{key: string, bn: string, en: string}
     */
    public static function differenceStatus(?float $difference): array
    {
        $difference = round((float) $difference, 2);

        return match (true) {
            $difference > 0 => ['key' => 'over', 'bn' => 'ক্যাশ বেশি', 'en' => 'Cash Over'],
            $difference < 0 => ['key' => 'short', 'bn' => 'ক্যাশ কম', 'en' => 'Cash Short'],
            default => ['key' => 'balanced', 'bn' => 'মিলেছে', 'en' => 'Balanced'],
        };
    }
}
