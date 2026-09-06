<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Core\Observers\AuditObserver;

class AccountTransaction extends Model
{
    use BelongsToShop, SoftDeletes;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = [
        'shop_id',
        'account_id',
        'type',
        'amount',
        'balance_after',
        'source',
        'sourceable_type',
        'sourceable_id',
        'note',
        'occurred_at',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    /**
     * @return array<string, array{bn: string, en: string}>
     */
    public static function sourceLabels(): array
    {
        return [
            'opening_balance' => ['bn' => 'প্রারম্ভিক ব্যালেন্স', 'en' => 'Opening Balance'],
            'sale' => ['bn' => 'বিক্রয়', 'en' => 'Sale'],
            'purchase' => ['bn' => 'ক্রয়', 'en' => 'Purchase'],
            'income' => ['bn' => 'আয়', 'en' => 'Income'],
            'expense' => ['bn' => 'ব্যয়', 'en' => 'Expense'],
            'sale_return' => ['bn' => 'বিক্রয় ফেরত', 'en' => 'Sale Return'],
            'purchase_return' => ['bn' => 'ক্রয় ফেরত', 'en' => 'Purchase Return'],
            'transfer_in' => ['bn' => 'ফান্ড গ্রহণ (ইন)', 'en' => 'Transfer In'],
            'transfer_out' => ['bn' => 'ফান্ড পাঠানো (আউট)', 'en' => 'Transfer Out'],
            'manual_adjustment' => ['bn' => 'ম্যানুয়াল সমন্বয়', 'en' => 'Manual Adjustment'],
        ];
    }

    /**
     * @return array{bn: string, en: string}
     */
    public function sourceLabel(): array
    {
        return static::sourceLabels()[$this->source] ?? ['bn' => $this->source, 'en' => $this->source];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
