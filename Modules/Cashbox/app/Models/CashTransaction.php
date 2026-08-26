<?php

namespace Modules\Cashbox\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Core\Observers\AuditObserver;

class CashTransaction extends Model
{
    use BelongsToShop, SoftDeletes;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = [
        'shop_id', 'type', 'source', 'sourceable_type', 'sourceable_id',
        'amount', 'note', 'occurred_at', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    /**
     * Bengali/English labels for each resource ("source") type.
     *
     * @return array<string, array{bn: string, en: string}>
     */
    public static function sourceLabels(): array
    {
        return [
            'manual' => ['bn' => 'ম্যানুয়াল', 'en' => 'Manual'],
            'sale' => ['bn' => 'বেচা', 'en' => 'Sale'],
            'purchase' => ['bn' => 'কেনা', 'en' => 'Purchase'],
            'income' => ['bn' => 'আয়', 'en' => 'Income'],
            'expense' => ['bn' => 'ব্যয়', 'en' => 'Expense'],
            'sale_return' => ['bn' => 'বিক্রয় ফেরত', 'en' => 'Sale Return'],
            'purchase_return' => ['bn' => 'ক্রয় ফেরত', 'en' => 'Purchase Return'],
        ];
    }

    public function sourceLabel(): array
    {
        return static::sourceLabels()[$this->source] ?? ['bn' => $this->source, 'en' => $this->source];
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
