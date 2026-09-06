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
            'users' => [
                'name' => 'ইউজার সীমা (Users Limit)',
                'description' => 'Maximum staff & admin accounts',
                'sort_order' => 1,
            ],
            'branches' => [
                'name' => 'শাখা সীমা (Branches Limit)',
                'description' => 'Maximum physical branches',
                'sort_order' => 2,
            ],
            'warehouses' => [
                'name' => 'গুদাম সীমা (Warehouses Limit)',
                'description' => 'Maximum warehouses / godowns',
                'sort_order' => 3,
            ],
            'products' => [
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
            'customers' => ['name' => 'গ্রাহক ও বাকি খাতা (Customers & Due)', 'sort_order' => 15],
            'suppliers' => ['name' => 'সরবরাহকারী (Suppliers)', 'sort_order' => 16],
            'income' => ['name' => 'আয় (Income)', 'sort_order' => 17],
            'expense' => ['name' => 'ব্যয় (Expense)', 'sort_order' => 18],
            'tax' => ['name' => 'ট্যাক্স ও ভ্যাট (Tax & VAT)', 'sort_order' => 19],
            'reports' => ['name' => 'রিপোর্ট ও অ্যানালিটিক্স (Reports)', 'sort_order' => 20],
            'audit' => ['name' => 'অ্যাক্টিভিটি লগ (Audit Log)', 'sort_order' => 21],
            'employees' => ['name' => 'কর্মচারী (Employees)', 'sort_order' => 22],
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
                    'users' => 2,
                    'branches' => 1,
                    'warehouses' => 1,
                    'products' => 200,
                ],
                'toggles' => ['sales', 'purchase', 'cashbox', 'quick-sale', 'stock', 'customers', 'suppliers', 'income', 'expense', 'reports'],
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
                    'users' => 5,
                    'branches' => 3,
                    'warehouses' => 3,
                    'products' => 2000,
                ],
                'toggles' => ['sales', 'purchase', 'cashbox', 'quick-sale', 'stock', 'customers', 'suppliers', 'income', 'expense', 'tax', 'reports', 'audit', 'employees'],
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
                    'users' => null, // 0 in Subscriptionify = unlimited
                    'branches' => null,
                    'warehouses' => null,
                    'products' => null,
                ],
                'toggles' => array_keys($toggleFeatures),
            ],
        ];

        foreach ($plans as $planData) {
            $limits = $planData['limits'];
            $toggles = $planData['toggles'];
            unset($planData['limits'], $planData['toggles']);

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
