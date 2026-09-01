<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\Features;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $plans = [
            [
                'name' => 'Starter', 'slug' => 'starter', 'price' => 0, 'billing_cycle' => 'monthly',
                'max_users' => 2, 'max_branches' => 1, 'max_warehouses' => 1, 'max_products' => 100,
                'features' => json_encode(['sales', 'purchase', 'stock', 'products', 'customers', 'suppliers', 'cashbox']),
                'status' => 'active',
            ],
            [
                'name' => 'Professional', 'slug' => 'professional', 'price' => 999, 'billing_cycle' => 'monthly',
                'max_users' => 10, 'max_branches' => 5, 'max_warehouses' => 10, 'max_products' => 5000,
                'features' => json_encode(Features::keys()),
                'status' => 'active',
            ],
            [
                'name' => 'Enterprise', 'slug' => 'enterprise', 'price' => 2999, 'billing_cycle' => 'monthly',
                'max_users' => null, 'max_branches' => null, 'max_warehouses' => null, 'max_products' => null,
                'features' => json_encode(Features::keys()),
                'status' => 'active',
            ],
        ];

        foreach ($plans as $plan) {
            $exists = DB::table('plans')->where('slug', $plan['slug'])->exists();

            if (! $exists) {
                $plan['created_at'] = now();
                $plan['updated_at'] = now();
                DB::table('plans')->insert($plan);
            }
        }

        // Every existing shop gets an active subscription to the Professional
        // plan, so nothing currently working is limited or blocked by a
        // subscription state that didn't exist until now.
        $professionalPlanId = DB::table('plans')->where('slug', 'professional')->value('id');

        foreach (DB::table('shops')->select('id')->get() as $shop) {
            $hasSubscription = DB::table('subscriptions')->where('shop_id', $shop->id)->exists();

            if (! $hasSubscription) {
                DB::table('subscriptions')->insert([
                    'shop_id' => $shop->id,
                    'plan_id' => $professionalPlanId,
                    'status' => 'active',
                    'current_period_start' => now()->startOfMonth(),
                    'current_period_end' => now()->endOfMonth(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('subscriptions')->delete();
        DB::table('plans')->whereIn('slug', ['starter', 'professional', 'enterprise'])->delete();
    }
};
