<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\DataTables\PlansDataTable;
use Modules\Shop\Models\Plan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlanDataTableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();
    }

    private function createSuperAdmin(): User
    {
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_plans_datatable_generates_html_builder(): void
    {
        $dataTable = new PlansDataTable;
        $html = $dataTable->html();

        $this->assertEquals('plans-data-table', $html->getTableAttribute('id'));
        $this->assertCount(9, $dataTable->getColumns());
    }

    public function test_plans_datatable_query_returns_query_builder(): void
    {
        $dataTable = new PlansDataTable;
        $query = $dataTable->query(new Plan);

        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_plans_index_page_loads_successfully_for_super_admin(): void
    {
        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->get(route('plans.index'));

        $response->assertOk();
        $response->assertSee('plans-data-table');
        $response->assertSee('নতুন প্ল্যান তৈরি করুন');
    }

    public function test_plans_datatable_ajax_returns_json(): void
    {
        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)
            ->getJson(route('plans.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);
    }

    public function test_plans_create_page_loads_successfully(): void
    {
        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->get(route('plans.create'));

        $response->assertOk();
        $response->assertSee('নতুন সাবস্ক্রিপশন প্ল্যান তৈরি');
        $response->assertSee('প্রাথমিক বিবরণ ও মূল্য নির্ধারণ');
        $response->assertSee('রিসোর্স কোটা ও সীমাবদ্ধতা');
        $response->assertSee('অন্তর্ভুক্ত ফিচার ও মডিউলসমূহ');
    }

    public function test_plan_can_be_created_via_post(): void
    {
        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->post(route('plans.store'), [
            'name' => 'Custom Pro Plan',
            'slug' => 'custom-pro-plan',
            'price' => 1500,
            'billing_cycle' => 'monthly',
            'max_users' => 10,
            'max_branches' => 3,
            'max_warehouses' => 2,
            'max_products' => 5000,
            'features' => ['sales', 'stock', 'customers'],
            'status' => 'active',
        ]);

        $response->assertRedirect(route('plans.index'));
        $this->assertDatabaseHas('plans', [
            'slug' => 'custom-pro-plan',
            'name' => 'Custom Pro Plan',
            'price' => 1500,
            'status' => 'active',
        ]);
    }
}
