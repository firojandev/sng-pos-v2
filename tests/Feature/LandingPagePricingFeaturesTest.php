<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Setting;
use Modules\Shop\Models\Plan;
use Revoltify\Subscriptionify\Models\Feature;
use Tests\TestCase;

class LandingPagePricingFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::setLandingPageEnabled(true);
        Setting::setRegistrationEnabled(true);
    }

    public function test_landing_page_limits_plan_features_and_displays_show_more_button(): void
    {
        $plan = Plan::create([
            'name' => 'Premium Test Plan',
            'slug' => 'premium-test-plan',
            'price' => 1200,
            'is_active' => true,
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ]);

        // Attach 8 features
        for ($i = 1; $i <= 8; $i++) {
            $feature = Feature::firstOrCreate([
                'slug' => "feature-item-{$i}",
            ], [
                'name' => "Feature Item {$i}",
                'type' => 'toggle',
            ]);
            $plan->features()->attach($feature->id, ['value' => '0']);
        }

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Premium Test Plan');
        // Extra features beyond 6 should have the lp-plan-feature-extra and is-hidden class
        $response->assertSee('lp-plan-feature-extra is-hidden');
        // Toggle button should be present
        $response->assertSee('lp-plan-features-toggle');
        $response->assertSee('+ আরও ২টি ফিচার দেখুন');
        $response->assertSee('+ Show 2 more features');
        $response->assertSee('কম ফিচার দেখুন');
        $response->assertSee('Show fewer features');
    }

    public function test_landing_page_does_not_show_toggle_button_when_plan_has_six_or_fewer_features(): void
    {
        Plan::query()->delete();

        $plan = Plan::create([
            'name' => 'Small Test Plan',
            'slug' => 'small-test-plan',
            'price' => 500,
            'is_active' => true,
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ]);

        // Attach 4 features
        for ($i = 1; $i <= 4; $i++) {
            $feature = Feature::firstOrCreate([
                'slug' => "mini-feature-{$i}",
            ], [
                'name' => "Mini Feature {$i}",
                'type' => 'toggle',
            ]);
            $plan->features()->attach($feature->id, ['value' => '0']);
        }

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Small Test Plan');
        $response->assertDontSee('lp-plan-feature-extra is-hidden');
        $response->assertDontSee('<div class="lp-plan-features-footer">', false);
        $response->assertDontSee('ফিচার দেখুন');
        $response->assertDontSee('more features');
    }

    public function test_feature_names_and_plan_names_with_parentheses_are_split_into_bn_and_en_spans(): void
    {
        Plan::query()->delete();

        $plan = Plan::create([
            'name' => 'স্ট্যান্ডার্ড (Standard)',
            'slug' => 'standard-split-test',
            'price' => 999,
            'is_active' => true,
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ]);

        $feature = Feature::firstOrCreate([
            'slug' => 'sales-split-test',
        ], [
            'name' => 'বিক্রয় (Sales)',
            'type' => 'toggle',
        ]);
        $plan->features()->attach($feature->id, ['value' => '0']);

        $response = $this->get('/');

        $response->assertOk();
        // Plan name should be split
        $response->assertSee('<span class="bn">স্ট্যান্ডার্ড</span>', false);
        $response->assertSee('<span class="en">Standard</span>', false);
        // Feature name should be split
        $response->assertSee('<span class="bn">বিক্রয়</span>', false);
        $response->assertSee('<span class="en">Sales</span>', false);
        // Should NOT show raw concatenated string inside the feature span
        $response->assertDontSee('<span>বিক্রয় (Sales)</span>', false);
    }

    public function test_landing_page_does_not_show_pricing_section_or_static_plan_card_when_no_plans_exist(): void
    {
        Plan::query()->delete();

        $response = $this->get('/');

        $response->assertOk();
        // Pricing section and static fallback card should not be rendered
        $response->assertDontSee('<section class="lp-pricing-section" id="pricing">', false);
        $response->assertDontSee('প্রো রিটেইল প্ল্যান');
        $response->assertDontSee('Pro Retail Plan');
        $response->assertDontSee('href="#pricing"', false);
    }

    public function test_landing_page_renders_popular_badge_and_respects_custom_popular_label(): void
    {
        Plan::query()->delete();

        $planPopularDefault = Plan::create([
            'name' => 'স্ট্যান্ডার্ড প্ল্যান (Standard Plan)',
            'slug' => 'standard-plan',
            'price' => 999,
            'is_active' => true,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'sort_order' => 1,
            'is_popular' => true,
            'popular_label' => null,
        ]);

        $planPopularCustom = Plan::create([
            'name' => 'এন্টারপ্রাইজ প্ল্যান (Enterprise Plan)',
            'slug' => 'enterprise-plan',
            'price' => 1999,
            'is_active' => true,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'sort_order' => 2,
            'is_popular' => true,
            'popular_label' => 'সেরা অফার (Best Value)',
        ]);

        $planNormal = Plan::create([
            'name' => 'বেসিক প্ল্যান (Basic Plan)',
            'slug' => 'basic-plan',
            'price' => 499,
            'is_active' => true,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'sort_order' => 0,
            'is_popular' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();

        // Default popular badge text
        $response->assertSee('<div class="lp-plan-tag"><span class="bn">সর্বাধিক জনপ্রিয়</span><span class="en">Most Popular</span></div>', false);

        // Custom popular badge text split into bn & en
        $response->assertSee('<div class="lp-plan-tag"><span class="bn">সেরা অফার</span><span class="en">Best Value</span></div>', false);

        // Order check: Basic (sort_order 0) appears before Standard (sort_order 1) and Enterprise (sort_order 2)
        $content = $response->getContent();
        $basicPos = strpos($content, 'basic-plan');
        $standardPos = strpos($content, 'standard-plan');
        $enterprisePos = strpos($content, 'enterprise-plan');

        $this->assertTrue($basicPos < $standardPos, 'Basic plan (sort_order 0) should appear before Standard plan (sort_order 1)');
        $this->assertTrue($standardPos < $enterprisePos, 'Standard plan (sort_order 1) should appear before Enterprise plan (sort_order 2)');
    }

    public function test_landing_page_renders_plan_quota_limitations_and_comparison_table(): void
    {
        Plan::query()->delete();

        $planA = Plan::create([
            'name' => 'স্টার্টার প্ল্যান (Starter Plan)',
            'slug' => 'starter-plan',
            'price' => 499,
            'is_active' => true,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'sort_order' => 1,
            'max_users' => 2,
            'max_branches' => 1,
            'max_warehouses' => 1,
            'max_products' => 500,
        ]);

        $planB = Plan::create([
            'name' => 'আল্টিমেট প্ল্যান (Ultimate Plan)',
            'slug' => 'ultimate-plan',
            'price' => 2500,
            'is_active' => true,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'sort_order' => 2,
            'max_users' => null, // Unlimited
            'max_branches' => null,
            'max_warehouses' => null,
            'max_products' => null,
        ]);

        $featurePos = Feature::firstOrCreate(['slug' => 'pos-terminal'], ['name' => 'পিওএস সেলস (POS Sales)', 'type' => 'toggle']);
        $featureAnalytics = Feature::firstOrCreate(['slug' => 'advanced-analytics'], ['name' => 'অ্যাডভান্সড অ্যানালিটিক্স (Advanced Analytics)', 'type' => 'toggle']);

        $planA->features()->attach($featurePos->id, ['value' => '0']);
        $planB->features()->attach([$featurePos->id => ['value' => '0'], $featureAnalytics->id => ['value' => '0']]);

        $response = $this->get('/');

        $response->assertOk();

        // 1. Quota Limitation strip on card
        $response->assertSee('lp-plan-quotas');
        $response->assertSee('৫০০'); // Starter products count in Bengali
        $response->assertSee('আনলিমিটেড'); // Ultimate plan unlimited quota in Bengali

        // 2. Full Side-by-Side Comparison Table
        $response->assertSee('id="btnToggleCompareTable"', false);
        $response->assertSee('id="lpCompareWrapper"', false);
        $response->assertSee('lp-compare-table');
        $response->assertSee('রিসোর্স কোটা ও সীমাবদ্ধতা');
        $response->assertSee('Resource Quotas and Limits');
        $response->assertSee('সর্বোচ্চ ব্যবহারকারী (Users)');
        $response->assertSee('সর্বোচ্চ আউটলেট / শাখা (Branches)');
        $response->assertSee('সিস্টেম ফিচার ও মডিউলসমূহ');
        $response->assertSee('System Features and Modules');
        $response->assertSee('পিওএস সেলস');
        $response->assertSee('অ্যাডভান্সড অ্যানালিটিক্স');
    }
}
