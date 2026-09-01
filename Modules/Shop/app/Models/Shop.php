<?php

namespace Modules\Shop\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Revoltify\Subscriptionify\Concerns\InteractsWithSubscriptions;
use Revoltify\Subscriptionify\Contracts\Subscribable;
use Revoltify\Subscriptionify\Enums\Interval;

class Shop extends Model implements Subscribable
{
    use InteractsWithSubscriptions {
        hasFeature as protected subscriptionifyHasFeature;
    }

    protected $fillable = [
        'name',
        'slug',
        'phone',
        'address',
        'status',
        'enabled_features',
    ];

    protected $casts = [
        'enabled_features' => 'array',
    ];

    public function admins(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Active subscription relationship.
     */
    public function activeSubscription(): MorphOne
    {
        return $this->morphOne(Subscription::class, 'subscribable')
            ->whereIn('status', ['active', 'trialing'])
            ->latestOfMany();
    }

    /**
     * Check if feature is available via Subscriptionify or shop manual override.
     */
    public function hasFeature(string $key): bool
    {
        // 1. If manual override is explicitly configured in shop
        if (is_array($this->enabled_features) && in_array($key, $this->enabled_features, true)) {
            return true;
        }

        // 2. Check via Subscriptionify plan & direct grants
        if ($this->subscribed()) {
            return $this->subscriptionifyHasFeature($key);
        }

        return false;
    }

    public function grantFeature(
        string $slug,
        ?int $value = null,
        ?string $unitPrice = null,
        ?int $resetPeriod = null,
        ?Interval $resetInterval = null,
    ): void {
        $this->unsetRelation('directFeatures');
        $this->featureGrantService()->grant(
            $this, $slug, $value, $unitPrice, $resetPeriod, $resetInterval,
        );
        $this->unsetRelation('directFeatures');
    }

    public function revokeFeature(string $slug): void
    {
        $this->unsetRelation('directFeatures');
        $this->featureGrantService()->revoke($this, $slug);
        $this->unsetRelation('directFeatures');
    }
}
