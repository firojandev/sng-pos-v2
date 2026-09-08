<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\DataTables\AuditLogDataTable;
use Modules\Core\Models\AuditLog;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Sales\Models\Sale;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogDataTableTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $adminUser;

    protected Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $this->adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create([
            'name' => 'Audit Test Shop',
            'slug' => 'audit-test-shop',
            'status' => 'active',
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        } else {
            $this->subscribeShopToFeatures($this->shop, Features::keys());
        }

        $this->adminUser = User::create([
            'name' => 'Audit Admin',
            'email' => 'auditadmin@test.com',
            'password' => bcrypt('password123'),
            'shop_id' => $this->shop->id,
            'email_verified_at' => now(),
        ]);
        $this->adminUser->syncRoles([$this->adminRole]);
    }

    public function test_audit_log_datatable_generates_html_builder(): void
    {
        $dataTable = new AuditLogDataTable;
        $html = $dataTable->html();

        $this->assertEquals('audit-log-table', $html->getTableAttribute('id'));
        $this->assertCount(7, $dataTable->getColumns());

        $columnData = collect($dataTable->getColumns())->pluck('data')->all();
        $this->assertContains('created_at', $columnData);
        $this->assertContains('user', $columnData);
        $this->assertContains('auditable_type', $columnData);
        $this->assertContains('action', $columnData);
        $this->assertContains('changes_summary', $columnData);
        $this->assertContains('client_info', $columnData);
        $this->assertContains('action_btn', $columnData);
    }

    public function test_audit_log_datatable_query_returns_query_builder(): void
    {
        $this->actingAs($this->adminUser);

        $dataTable = new AuditLogDataTable;
        $query = $dataTable->query(new AuditLog);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_audit_log_index_page_loads_with_datatable_and_stat_cards(): void
    {
        AuditLog::create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->adminUser->id,
            'auditable_type' => Sale::class,
            'auditable_id' => 101,
            'action' => 'created',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'old_values' => null,
            'new_values' => ['invoice_no' => 'INV-001', 'total' => 1500],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('audit-log.index'));

        $response->assertOk();
        $response->assertSee('audit-log-table');
        $response->assertSee('auditDetailDrawer');
        $response->assertSee('সর্বমোট অ্যাক্টিভিটি');
        $response->assertSee('আজকের অ্যাক্টিভিটি');
        $response->assertSee('নতুন এন্ট্রি তৈরি');
        $response->assertSee('filter-model');
        $response->assertSee('filter-action');
    }

    public function test_audit_log_datatable_ajax_returns_json(): void
    {
        AuditLog::create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->adminUser->id,
            'auditable_type' => Sale::class,
            'auditable_id' => 102,
            'action' => 'updated',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'old_values' => ['total' => 1000],
            'new_values' => ['total' => 1200],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('audit-log.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);
        $this->assertGreaterThanOrEqual(1, $response->json('recordsTotal'));
    }

    public function test_audit_log_datatable_filters_by_action(): void
    {
        AuditLog::create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->adminUser->id,
            'auditable_type' => Sale::class,
            'auditable_id' => 201,
            'action' => 'deleted',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'old_values' => ['invoice_no' => 'INV-DEL'],
            'new_values' => null,
            'created_at' => now(),
        ]);

        AuditLog::create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->adminUser->id,
            'auditable_type' => Customer::class,
            'auditable_id' => 301,
            'action' => 'created',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'old_values' => null,
            'new_values' => ['name' => 'John Doe'],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('audit-log.index', ['filter_action' => 'deleted']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('মুছে ফেলা', json_encode($response->json('data'), JSON_UNESCAPED_UNICODE));
    }

    public function test_audit_log_datatable_filters_by_model(): void
    {
        AuditLog::create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->adminUser->id,
            'auditable_type' => Customer::class,
            'auditable_id' => 401,
            'action' => 'created',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'old_values' => null,
            'new_values' => ['name' => 'Specific Customer'],
            'created_at' => now(),
        ]);

        AuditLog::create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->adminUser->id,
            'auditable_type' => Sale::class,
            'auditable_id' => 402,
            'action' => 'created',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'old_values' => null,
            'new_values' => ['invoice_no' => 'INV-XYZ'],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('audit-log.index', ['filter_model' => Customer::class]), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('গ্রাহক', json_encode($response->json('data'), JSON_UNESCAPED_UNICODE));
    }

    public function test_audit_log_show_endpoint_returns_diff_and_forensics(): void
    {
        $log = AuditLog::create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->adminUser->id,
            'auditable_type' => Sale::class,
            'auditable_id' => 501,
            'action' => 'updated',
            'ip_address' => '192.168.1.50',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0',
            'old_values' => ['paid_amount' => 500, 'status' => 'pending'],
            'new_values' => ['paid_amount' => 1000, 'status' => 'completed'],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('audit-log.show', $log), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'action' => 'updated',
                'ip_address' => '192.168.1.50',
                'platform' => 'Windows',
                'browser' => 'Chrome',
            ],
        ]);

        $response->assertJsonStructure([
            'diff',
            'raw_old_json',
            'raw_new_json',
            'target_record',
        ]);

        $diffFields = collect($response->json('diff'))->pluck('field')->all();
        $this->assertContains('paid_amount', $diffFields);
        $this->assertContains('status', $diffFields);
    }

    public function test_audit_observer_automatically_logs_model_events_with_snapshots(): void
    {
        $this->actingAs($this->adminUser);

        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Auto Audit Customer',
            'phone' => '01712345678',
            'email' => 'autoaudit@test.com',
            'opening_due' => 500,
            'status' => 'active',
        ]);

        $createdLog = AuditLog::where('auditable_type', Customer::class)
            ->where('auditable_id', $customer->id)
            ->where('action', 'created')
            ->first();

        $this->assertNotNull($createdLog);
        $this->assertEquals($this->adminUser->id, $createdLog->user_id);
        $this->assertEquals('Auto Audit Customer', $createdLog->new_values['name']);

        $customer->update(['name' => 'Updated Audit Customer', 'opening_due' => 800]);

        $updatedLog = AuditLog::where('auditable_type', Customer::class)
            ->where('auditable_id', $customer->id)
            ->where('action', 'updated')
            ->first();

        $this->assertNotNull($updatedLog);
        $this->assertEquals('Auto Audit Customer', $updatedLog->old_values['name']);
        $this->assertEquals('Updated Audit Customer', $updatedLog->new_values['name']);
    }
}
