<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Revoltify\Subscriptionify\Enums\SubscriptionStatus;
use Revoltify\Subscriptionify\Models\Subscription as BaseSubscription;

class Subscription extends BaseSubscription
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'subscribable_type',
        'subscribable_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'cancelled_at',
        'renewed_at',
        'shop_id',
        'current_period_start',
        'current_period_end',
    ];

    /**
     * Bengali/English labels for each subscription status.
     *
     * @return array<string, array{bn: string, en: string}>
     */
    public static function statusLabels(): array
    {
        return [
            'trialing' => ['bn' => 'ট্রায়াল', 'en' => 'Trial'],
            'trial' => ['bn' => 'ট্রায়াল', 'en' => 'Trial'],
            'active' => ['bn' => 'সক্রিয়', 'en' => 'Active'],
            'past_due' => ['bn' => 'বকেয়া', 'en' => 'Past Due'],
            'suspended' => ['bn' => 'স্থগিত', 'en' => 'Suspended'],
            'cancelled' => ['bn' => 'বাতিল', 'en' => 'Cancelled'],
            'expired' => ['bn' => 'মেয়াদোত্তীর্ণ', 'en' => 'Expired'],
        ];
    }

    public function statusLabel(): array
    {
        $statusKey = $this->status instanceof SubscriptionStatus ? $this->status->value : (string) $this->status;

        return static::statusLabels()[$statusKey] ?? ['bn' => $statusKey, 'en' => $statusKey];
    }

    /**
     * Whether the shop should currently be allowed to use the app.
     */
    public function isUsable(): bool
    {
        if ($this->status === SubscriptionStatus::Cancelled || $this->status === SubscriptionStatus::Expired) {
            return $this->onGracePeriod();
        }

        if ($this->status === SubscriptionStatus::Trialing && $this->trial_ends_at && $this->trial_ends_at->isPast()) {
            return false;
        }

        if ($this->status === SubscriptionStatus::PastDue && $this->ends_at && $this->ends_at->isPast()) {
            return $this->onGracePeriod();
        }

        return $this->valid();
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'subscribable_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }
}
