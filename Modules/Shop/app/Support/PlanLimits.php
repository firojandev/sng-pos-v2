<?php

namespace Modules\Shop\Support;

use Modules\Shop\Models\Shop;

class PlanLimits
{
    /**
     * Check whether creating one more of a limited resource would exceed the
     * shop's plan. Returns a user-facing Bengali message when blocked, or
     * null when allowed.
     */
    public static function check(?int $shopId, string $limitKey, int $currentCount): ?string
    {
        if (! $shopId) {
            return null;
        }

        $shop = Shop::find($shopId);
        if (! $shop) {
            return null;
        }

        $featureSlug = str_replace('max_', '', $limitKey);

        if ($shop->subscribed()) {
            if ($shop->isUnlimitedUsage($featureSlug)) {
                return null;
            }

            if (! $shop->canConsume($featureSlug, 1)) {
                $planName = $shop->subscription()?->getPlan()?->getName() ?? 'বর্তমান';

                return "আপনার '{$planName}' প্ল্যানে এই রিসোর্সের সীমা পূর্ণ হয়েছে। আরও যোগ করতে প্ল্যান আপগ্রেড করুন।";
            }
        }

        $subscription = $shop->subscription();
        if ($subscription && $subscription->plan) {
            $limit = $subscription->plan->{$limitKey} ?? null;
            if ($limit !== null && $currentCount >= $limit) {
                return "আপনার '{$subscription->plan->name}' প্ল্যানে সর্বোচ্চ {$limit}টি অনুমোদিত। আরও যোগ করতে প্ল্যান আপগ্রেড করুন।";
            }
        }

        return null;
    }
}
