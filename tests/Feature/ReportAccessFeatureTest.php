<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Permissions;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportAccessFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    private function createShopUserWithReport(string $reportKey): User
    {
        $shop = Shop::create([
            'name' => 'Report Test Shop',
            'slug' => 'report-test-shop-'.$reportKey,
            'status' => 'active',
        ]);
        $this->subscribeShopToFeatures($shop, [$reportKey]);

        $role = Role::firstOrCreate(['shop_id' => $shop->id, 'name' => 'Admin', 'guard_name' => 'web']);
        $role->syncPermissions(["{$reportKey}.view", "{$reportKey}.print"]);

        $user = User::factory()->create(['shop_id' => $shop->id]);
        setPermissionsTeamId($shop->id);
        $user->assignRole($role);
        setPermissionsTeamId(null);

        return $user;
    }

    public function test_shop_with_only_sales_report_can_access_it_but_not_purchase_report(): void
    {
        $user = $this->createShopUserWithReport('report-sales');

        $this->actingAs($user)->get(route('reports.sales'))->assertOk();
        $this->actingAs($user)->get(route('reports.purchase'))->assertForbidden();
    }

    public function test_reports_hub_only_lists_granted_reports(): void
    {
        $user = $this->createShopUserWithReport('report-sales');

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Sales Report');
        $response->assertDontSee('Purchase Report');
    }

    public function test_sidebar_only_shows_granted_report_links(): void
    {
        $user = $this->createShopUserWithReport('report-income');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('reports.income'));
        $response->assertDontSee(route('reports.expense'));
    }
}
