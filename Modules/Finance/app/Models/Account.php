<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Core\Observers\AuditObserver;
use Modules\Shop\Models\Shop;

class Account extends Model
{
    use BelongsToShop, SoftDeletes;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = [
        'shop_id',
        'name',
        'type',
        'account_number',
        'bank_name',
        'branch_name',
        'mfs_provider',
        'mfs_type',
        'opening_balance',
        'current_balance',
        'is_default',
        'status',
        'note',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    /**
     * @return array<string, array{bn: string, en: string}>
     */
    public static function typeLabels(): array
    {
        return [
            'cash' => ['bn' => 'নগদ', 'en' => 'Cash'],
            'bank' => ['bn' => 'ব্যাংক', 'en' => 'Bank'],
            'mfs' => ['bn' => 'মোবাইল ব্যাংকিং', 'en' => 'Mobile Banking (MFS)'],
        ];
    }

    /**
     * Types that can be manually created by users.
     *
     * @return array<string, array{bn: string, en: string}>
     */
    public static function creatableTypeLabels(): array
    {
        return [
            'bank' => ['bn' => 'ব্যাংক', 'en' => 'Bank'],
            'mfs' => ['bn' => 'মোবাইল ব্যাংকিং', 'en' => 'Mobile Banking (MFS)'],
        ];
    }

    public function isCash(): bool
    {
        return $this->type === 'cash';
    }

    /**
     * @return array{bn: string, en: string}
     */
    public function typeLabel(): array
    {
        return static::typeLabels()[$this->type] ?? ['bn' => $this->type, 'en' => $this->type];
    }

    /**
     * @return array<string, array{bn: string, en: string}>
     */
    public static function mfsTypeLabels(): array
    {
        return [
            'personal' => ['bn' => 'পার্সোনাল', 'en' => 'Personal'],
            'merchant' => ['bn' => 'মার্চেন্ট', 'en' => 'Merchant'],
            'agent' => ['bn' => 'এজেন্ট', 'en' => 'Agent'],
        ];
    }

    /**
     * @return array{bn: string, en: string}|null
     */
    public function mfsTypeLabel(): ?array
    {
        return $this->mfs_type ? (static::mfsTypeLabels()[$this->mfs_type] ?? ['bn' => $this->mfs_type, 'en' => $this->mfs_type]) : null;
    }

    /**
     * @return array<string, string>
     */
    public static function mfsProviders(): array
    {
        return [
            'bkash' => 'bKash (বিকাশ)',
            'nagad' => 'Nagad (নগদ)',
            'rocket' => 'Rocket (রকেট)',
            'upay' => 'Upay (উপায়)',
            'cellfin' => 'Cellfin (সেলফিন)',
            'other' => 'Other (অন্যান্য)',
        ];
    }

    public function getDisplayNameAttribute(): string
    {
        $details = [];
        if ($this->type === 'bank' && $this->bank_name) {
            $details[] = $this->bank_name;
        }
        if ($this->account_number) {
            $details[] = $this->account_number;
        }

        $extra = ! empty($details) ? ' ('.implode(' - ', $details).')' : '';

        return $this->name.$extra;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class)->orderByDesc('occurred_at')->orderByDesc('id');
    }

    public function transfersFrom(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'from_account_id');
    }

    public function transfersTo(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'to_account_id');
    }
}
