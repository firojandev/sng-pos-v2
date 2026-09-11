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

    private function createShopUserWithReport(string $reportKey, ?array $permissions = null): User
    {
        $shop = Shop::create([
            'name' => 'Report Test Shop',
            'slug' => 'report-test-shop-'.$reportKey.'-'.uniqid(),
            'status' => 'active',
        ]);
        $this->subscribeShopToFeatures($shop, [$reportKey]);

        $role = Role::firstOrCreate(['shop_id' => $shop->id, 'name' => 'Admin', 'guard_name' => 'web']);
        $role->syncPermissions($permissions ?? ["{$reportKey}.view", "{$reportKey}.print"]);

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

    public function test_report_renders_today_filter_and_pdf_export_button(): void
    {
        $user = $this->createShopUserWithReport('report-sales');

        $response = $this->actingAs($user)->get(route('reports.sales', ['range' => 'today']));

        $response->assertOk();
        $response->assertSee('আজ');
        $response->assertSee('Today');
        $response->assertSee('btn-report-export-pdf');
        $response->assertSee('পিডিএফ এক্সপোর্ট');
        $response->assertSee('Export PDF');
    }

    public function test_reports_default_to_today_filter(): void
    {
        $user = $this->createShopUserWithReport('report-sales');

        $response = $this->actingAs($user)->get(route('reports.sales'));

        $response->assertOk();
        $response->assertViewHas('range', 'today');
        $response->assertViewHas('from', now()->toDateString());
        $response->assertViewHas('to', now()->toDateString());
    }

    public function test_reports_have_table_headers(): void
    {
        $salesUser = $this->createShopUserWithReport('report-sales');
        $salesResponse = $this->actingAs($salesUser)->get(route('reports.sales'));
        $salesResponse->assertOk();
        $salesResponse->assertSee('<thead>', false);
        $salesResponse->assertSee('Invoice');

        $plUser = $this->createShopUserWithReport('report-profit-loss');
        $plResponse = $this->actingAs($plUser)->get(route('reports.profit-loss'));
        $plResponse->assertOk();
        $plResponse->assertSee('<thead>', false);
        $plResponse->assertSee('খাত / বিবরণ');
        $plResponse->assertSee('Particulars / Description');
        $plResponse->assertSee('পরিমাণ');
        $plResponse->assertSee('Amount');
    }

    public function test_report_pdf_export_button_hidden_when_user_lacks_print_permission(): void
    {
        $user = $this->createShopUserWithReport('report-sales', ['report-sales.view']);

        $response = $this->actingAs($user)->get(route('reports.sales'));

        $response->assertOk();
        $response->assertSee('মোট বিক্রয়');
        $response->assertDontSee('btn-report-export-pdf');
        $response->assertDontSee('পিডিএফ এক্সপোর্ট');
        $response->assertDontSee('Export PDF');
    }
}
