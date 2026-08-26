<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'shop_id', 'plan_id', 'status', 'trial_ends_at',
        'current_period_start', 'current_period_end', 'cancelled_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'date',
        'current_period_start' => 'date',
        'current_period_end' => 'date',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Bengali/English labels for each subscription status.
     *
     * @return array<string, array{bn: string, en: string}>
     */
    public static function statusLabels(): array
    {
        return [
            'trial' => ['bn' => 'ট্রায়াল', 'en' => 'Trial'],
            'active' => ['bn' => 'সক্রিয়', 'en' => 'Active'],
            'past_due' => ['bn' => 'বকেয়া', 'en' => 'Past Due'],
            'suspended' => ['bn' => 'স্থগিত', 'en' => 'Suspended'],
            'cancelled' => ['bn' => 'বাতিল', 'en' => 'Cancelled'],
        ];
    }

    public function statusLabel(): array
    {
        return static::statusLabels()[$this->status] ?? ['bn' => $this->status, 'en' => $this->status];
    }

    /**
     * Whether the shop should currently be allowed to use the app.
     * Trial/active are always fine; past_due gets a grace period via the
     * still-valid current_period_end; suspended/cancelled are blocked.
     */
    public function isUsable(): bool
    {
        if ($this->status === 'suspended' || $this->status === 'cancelled') {
            return false;
        }

        if ($this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isPast()) {
            return false;
        }

        if ($this->status === 'past_due' && $this->current_period_end && $this->current_period_end->isPast()) {
            return false;
        }

        return true;
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }
}
