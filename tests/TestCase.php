<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Revoltify\Subscriptionify\Enums\FeatureType;
use Revoltify\Subscriptionify\Models\Feature;

abstract class TestCase extends BaseTestCase
{
    /**
     * Grant a shop access to the given feature keys by creating a
     * throwaway Plan, syncing Subscriptionify Feature rows for those keys,
     * and subscribing the shop to it. Mirrors what ShopController::store()
     * and PlanController::syncFeatures() do for real plan assignment.
     *
     * @param  string[]  $featureKeys
     */
    protected function subscribeShopToFeatures(Shop $shop, array $featureKeys): void
    {
        $plan = Plan::create([
            'name' => 'Test Plan '.Str::random(6),
            'slug' => Str::slug('test-plan-'.Str::random(8)),
            'price' => 0,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $ids = collect($featureKeys)->map(fn (string $slug) => Feature::firstOrCreate(
            ['slug' => $slug],
            ['name' => $slug, 'type' => FeatureType::Toggle],
        )->id);

        $plan->features()->sync($ids->mapWithKeys(fn ($id) => [$id => ['value' => '0']]));

        $shop->subscriptions()->create([
            'subscribable_type' => Shop::class,
            'subscribable_id' => $shop->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'current_period_start' => now(),
            'current_period_end' => now()->addYear(),
        ]);

        $shop->clearSubscriptionCache();
    }
}
