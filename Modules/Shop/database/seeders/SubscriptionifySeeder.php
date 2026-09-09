<?php

namespace Modules\Shop\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Shop\Models\Plan;
use Revoltify\Subscriptionify\Enums\FeatureType;
use Revoltify\Subscriptionify\Enums\Interval;
use Revoltify\Subscriptionify\Models\Feature;

class SubscriptionifySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create or Update Limit Features
        $limitFeatures = [
            'max-users' => [
                'name' => 'ইউজার সীমা (Users Limit)',
                'description' => 'Maximum staff & admin accounts',
                'sort_order' => 1,
            ],
            'max-branches' => [
                'name' => 'শাখা সীমা (Branches Limit)',
                'description' => 'Maximum physical branches',
                'sort_order' => 2,
            ],
            'max-warehouses' => [
                'name' => 'গুদাম সীমা (Warehouses Limit)',
                'description' => 'Maximum warehouses / godowns',
                'sort_order' => 3,
            ],
            'max-products' => [
                'name' => 'পণ্য সীমা (Products Limit)',
                'description' => 'Maximum product catalog items',
                'sort_order' => 4,
            ],
        ];

        foreach ($limitFeatures as $slug => $data) {
            Feature::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'type' => FeatureType::Limit,
                    'sort_order' => $data['sort_order'],
                ]
            );
        }

        // 2. Create or Update Toggle Features
        $toggleFeatures = [
            'sales' => ['name' => 'বিক্রয় (Sales)', 'sort_order' => 10],
            'purchase' => ['name' => 'ক্রয় (Purchase)', 'sort_order' => 11],
            'cashbox' => ['name' => 'ক্যাশবক্স (Cashbox)', 'sort_order' => 12],
            'quick-sale' => ['name' => 'দ্রুত বেচা (Quick Sale POS)', 'sort_order' => 13],
            'stock' => ['name' => 'স্টক ট্র্যাকিং (Stock Tracking)', 'sort_order' => 14],
            'products' => ['name' => 'পণ্য ব্যবস্থাপনা (Product Management)', 'sort_order' => 15],
            'branches' => ['name' => 'শাখা ও গুদাম (Branches & Warehouses)', 'sort_order' => 16],
            'customers' => ['name' => 'গ্রাহক ও বাকি খাতা (Customers & Due)', 'sort_order' => 17],
            'suppliers' => ['name' => 'সরবরাহকারী (Suppliers)', 'sort_order' => 18],
            'income' => ['name' => 'আয় (Income)', 'sort_order' => 19],
            'expense' => ['name' => 'ব্যয় (Expense)', 'sort_order' => 20],
            'accounts' => ['name' => 'অ্যাকাউন্ট (Accounts)', 'sort_order' => 21],
            'account-transfers' => ['name' => 'ফান্ড ট্রান্সফার (Fund Transfers)', 'sort_order' => 22],
            'tax' => ['name' => 'ট্যাক্স ও ভ্যাট (Tax & VAT)', 'sort_order' => 23],
            'report-sales' => ['name' => 'বিক্রয় রিপোর্ট (Sales Report)', 'sort_order' => 24],
            'report-purchase' => ['name' => 'ক্রয় রিপোর্ট (Purchase Report)', 'sort_order' => 25],
            'report-stock' => ['name' => 'স্টক রিপোর্ট (Stock Report)', 'sort_order' => 26],
            'report-products' => ['name' => 'পণ্য রিপোর্ট (Product Report)', 'sort_order' => 27],
            'report-profit-loss' => ['name' => 'লাভ-ক্ষতি রিপোর্ট (Profit & Loss Report)', 'sort_order' => 28],
            'report-income' => ['name' => 'আয় রিপোর্ট (Income Report)', 'sort_order' => 29],
            'report-expense' => ['name' => 'ব্যয় রিপোর্ট (Expense Report)', 'sort_order' => 30],
            'audit' => ['name' => 'অ্যাক্টিভিটি লগ (Audit Log)', 'sort_order' => 31],
            'employees' => ['name' => 'কর্মচারী (Employees)', 'sort_order' => 32],
            'users' => ['name' => 'ইউজার (Users)', 'sort_order' => 33],
            'subscription' => ['name' => 'সাবস্ক্রিপশন ও প্ল্যান (Subscription & Plan)', 'sort_order' => 34],
        ];

        foreach ($toggleFeatures as $slug => $data) {
            Feature::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['name'],
                    'type' => FeatureType::Toggle,
                    'sort_order' => $data['sort_order'],
                ]
            );
        }

        $allFeatures = Feature::all()->keyBy('slug');

        // 3. Define Standard Plans
        $plans = [
            [
                'name' => 'বেসিক (Starter)',
                'slug' => 'starter',
                'description' => 'ছোট দোকান বা নতুন ব্যবসার জন্য উপযুক্ত',
                'price' => 999.00,
                'is_free' => false,
                'is_active' => true,
                'trial_days' => 14,
                'billing_period' => 1,
                'billing_interval' => Interval::Month,
                'grace_days' => 3,
                'sort_order' => 1,
                'status' => 'active',
                'limits' => [
                    'max-users' => 2,
                    'max-branches' => 1,
                    'max-warehouses' => 1,
                    'max-products' => 200,
                ],
                'toggles' => ['sales', 'purchase', 'cashbox', 'quick-sale', 'stock', 'products', 'branches', 'customers', 'suppliers', 'income', 'expense', 'report-sales', 'subscription'],
            ],
            [
                'name' => 'স্ট্যান্ডার্ড (Standard)',
                'slug' => 'standard',
                'description' => 'মাঝারি ও বর্ধনশীল খুচরা ব্যবসার জন্য সেরা',
                'price' => 2499.00,
                'is_free' => false,
                'is_active' => true,
                'trial_days' => 7,
                'billing_period' => 1,
                'billing_interval' => Interval::Month,
                'grace_days' => 5,
                'sort_order' => 2,
                'status' => 'active',
                'limits' => [
                    'max-users' => 5,
                    'max-branches' => 3,
                    'max-warehouses' => 3,
                    'max-products' => 2000,
                ],
                'toggles' => ['sales', 'purchase', 'cashbox', 'quick-sale', 'stock', 'products', 'branches', 'customers', 'suppliers', 'income', 'expense', 'accounts', 'account-transfers', 'tax', 'report-sales', 'report-purchase', 'report-stock', 'report-products', 'report-profit-loss', 'report-income', 'report-expense', 'audit', 'employees', 'users', 'subscription'],
            ],
            [
                'name' => 'প্রিমিয়াম (Enterprise)',
                'slug' => 'enterprise',
                'description' => 'বৃহৎ চেইন ও একাধিক আউটলেটের জন্য সীমাহীন সুবিধা',
                'price' => 4999.00,
                'is_free' => false,
                'is_active' => true,
                'trial_days' => 0,
                'billing_period' => 1,
                'billing_interval' => Interval::Month,
                'grace_days' => 7,
                'sort_order' => 3,
                'status' => 'active',
                'limits' => [
                    'max-users' => null, // 0 in Subscriptionify = unlimited
                    'max-branches' => null,
                    'max-warehouses' => null,
                    'max-products' => null,
                ],
                'toggles' => array_keys($toggleFeatures),
            ],
        ];

        foreach ($plans as $planData) {
            $limits = $planData['limits'];
            $toggles = $planData['toggles'];
            unset($planData['limits'], $planData['toggles']);

            $planData['max_users'] = $limits['max-users'] ?? null;
            $planData['max_branches'] = $limits['max-branches'] ?? null;
            $planData['max_warehouses'] = $limits['max-warehouses'] ?? null;
            $planData['max_products'] = $limits['max-products'] ?? null;

            $plan = Plan::updateOrCreate(['slug' => $planData['slug']], $planData);

            $attachData = [];

            // Limits ('0' in Subscriptionify means unlimited)
            foreach ($limits as $slug => $limitVal) {
                if (isset($allFeatures[$slug])) {
                    $attachData[$allFeatures[$slug]->id] = [
                        'value' => (string) ($limitVal ?? '0'),
                    ];
                }
            }

            // Toggles ('0' is standard value for toggles)
            foreach ($toggles as $slug) {
                if (isset($allFeatures[$slug])) {
                    $attachData[$allFeatures[$slug]->id] = [
                        'value' => '0',
                    ];
                }
            }

            $plan->features()->sync($attachData);
        }
    }
}
