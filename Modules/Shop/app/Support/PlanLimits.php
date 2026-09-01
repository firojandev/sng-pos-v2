<?php

namespace Modules\Shop\Support;

use Modules\Shop\Models\Subscription;

class PlanLimits
{
    /**
     * Check whether creating one more of a limited resource would exceed the
     * shop's plan. Returns a user-facing Bengali message when blocked, or
     * null when allowed (including shops with no subscription at all, so
     * existing installs keep working without one).
     */
    public static function check(?int $shopId, string $limitKey, int $currentCount): ?string
    {
        if (! $shopId) {
            return null;
        }

        $subscription = Subscription::where('shop_id', $shopId)->with('plan')->first();

        if (! $subscription || ! $subscription->plan) {
            return null;
        }

        $limit = $subscription->plan->{$limitKey};

        if ($limit === null || $currentCount < $limit) {
            return null;
        }

        return "আপনার '{$subscription->plan->name}' প্ল্যানে সর্বোচ্চ {$limit}টি অনুমোদিত। আরও যোগ করতে প্ল্যান আপগ্রেড করুন।";
    }
}
