<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\AuditLog;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Subscription;
use Modules\Shop\Models\SubscriptionPayment;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function createSuperAdmin(): User
    {
        $role = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
            'shop_id' => null,
        ]);

        $user = User::factory()->create([
            'shop_id' => null,
        ]);

        \DB::table('model_has_roles')->insert([
            'role_id' => $role->id,
            'model_type' => User::class,
            'model_id' => $user->id,
            'shop_id' => 0,
        ]);

        return $user;
    }

    public function test_super_admin_can_view_super_admin_dashboard(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $shop = Shop::create([
            'name' => 'আলতাব ট্রেডার্স',
            'slug' => 'altab-traders',
            'store_code' => 'shop-001',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'name' => 'ব্যবসায়ী প্রো',
            'slug' => 'business-pro',
            'price' => 1999.00,
            'is_active' => true,
            'status' => 'active',
        ]);

        $sub = Subscription::create([
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'subscribable_type' => Shop::class,
            'subscribable_id' => $shop->id,
            'status' => 'active',
            'starts_at' => now()->startOfMonth(),
            'ends_at' => now()->addDays(10),
        ]);

        SubscriptionPayment::create([
            'subscription_id' => $sub->id,
            'amount' => 1999.00,
            'method' => 'bkash',
            'paid_at' => now()->toDateString(),
        ]);

        $response = $this->actingAs($superAdmin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('core::superadmin-dashboard');

        $response->assertViewHasAll([
            'totalShops',
            'activeShops',
            'inactiveShops',
            'newShopsPeriod',
            'revenuePeriod',
            'revenueAllTime',
            'activeSubscriptions',
            'trialSubscriptions',
            'expiredSubscriptions',
            'totalUsers',
            'totalPlans',
            'chartLabels',
            'chartShopsData',
            'chartRevenueData',
            'statusCounts',
            'plans',
            'expiringSubscriptions',
            'recentShops',
            'recentPayments',
            'recentAuditLogs',
        ]);

        $response->assertSee('সুপার অ্যাডমিন ড্যাশবোর্ড');
        $response->assertSee('Super Admin Dashboard');
        $response->assertSee('আলতাব ট্রেডার্স');
        $response->assertSee('ব্যবসায়ী প্রো');
        $response->assertSee('1,999.00');
    }

    public function test_super_admin_dashboard_supports_range_filtering(): void
    {
        $superAdmin = $this->createSuperAdmin();

        foreach (['today', 'week', 'month', 'year', 'all'] as $range) {
            $response = $this->actingAs($superAdmin)->get(route('dashboard', ['range' => $range]));
            $response->assertStatus(200);
            $response->assertViewIs('core::superadmin-dashboard');
            $response->assertViewHas('range', $range);
        }
    }

    public function test_regular_user_views_standard_shop_dashboard(): void
    {
        $shop = Shop::create([
            'name' => 'রেগুলার শপ',
            'slug' => 'regular-shop',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'shop_id' => $shop->id,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('core::dashboard');
    }

    public function test_super_admin_can_access_audit_log(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get(route('audit-log.index'));

        $response->assertStatus(200);
    }

    public function test_super_admin_can_fetch_audit_log_datatable_and_details(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $shop = Shop::create([
            'name' => 'টেস্ট শপ',
            'slug' => 'test-shop',
            'status' => 'active',
        ]);

        $log = AuditLog::create([
            'shop_id' => $shop->id,
            'user_id' => $superAdmin->id,
            'auditable_type' => 'App\Models\User',
            'auditable_id' => $superAdmin->id,
            'action' => 'created',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'new_values' => ['name' => 'Super Admin'],
        ]);

        $dataTableResponse = $this->actingAs($superAdmin)->getJson(route('audit-log.index'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $dataTableResponse->assertStatus(200);

        $detailResponse = $this->actingAs($superAdmin)->getJson(route('audit-log.show', $log), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $detailResponse->assertStatus(200);
        $detailResponse->assertJsonPath('success', true);
        $detailResponse->assertJsonPath('log.id', $log->id);
    }
}
