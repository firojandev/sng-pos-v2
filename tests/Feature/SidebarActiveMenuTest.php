<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SidebarActiveMenuTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create([
            'name' => 'Gadget Store',
            'slug' => 'gadget-store',
            'phone' => '01778623121',
            'address' => 'Dhaka',
            'status' => 'active',
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }
        $this->subscribeShopToFeatures($this->shop, Features::keys());

        setPermissionsTeamId($this->shop->id);

        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@gadgetstore.test',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);

        Branch::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Branch',
            'status' => 'active',
        ]);
    }

    public function test_when_active_is_sales_ledger_sales_menu_is_not_active(): void
    {
        $this->actingAs($this->user);
        $html = (string) $this->blade('<x-core::sidebar active="sales-ledger" />');

        // sales-ledger must have active class
        $this->assertStringContainsString(route('sales.ledger').'" class="nav-item active"', $html);

        // sales must NOT have active class
        $this->assertStringNotContainsString(route('sales.index').'" class="nav-item active"', $html);
        $this->assertStringContainsString(route('sales.index').'" class="nav-item "', $html);
    }

    public function test_when_active_is_sales_sales_ledger_is_not_active(): void
    {
        $this->actingAs($this->user);
        $html = (string) $this->blade('<x-core::sidebar active="sales" />');

        // sales must have active class
        $this->assertStringContainsString(route('sales.index').'" class="nav-item active"', $html);

        // sales-ledger must NOT have active class
        $this->assertStringNotContainsString(route('sales.ledger').'" class="nav-item active"', $html);
        $this->assertStringContainsString(route('sales.ledger').'" class="nav-item "', $html);
    }

    public function test_when_active_is_purchase_ledger_purchase_menu_is_not_active(): void
    {
        $this->actingAs($this->user);
        $html = (string) $this->blade('<x-core::sidebar active="purchase-ledger" />');

        // purchase-ledger must have active class
        $this->assertStringContainsString(route('purchase.ledger').'" class="nav-item active"', $html);

        // purchase must NOT have active class
        $this->assertStringNotContainsString(route('purchase.index').'" class="nav-item active"', $html);
        $this->assertStringContainsString(route('purchase.index').'" class="nav-item "', $html);
    }

    public function test_when_active_is_purchase_purchase_ledger_is_not_active(): void
    {
        $this->actingAs($this->user);
        $html = (string) $this->blade('<x-core::sidebar active="purchase" />');

        // purchase must have active class
        $this->assertStringContainsString(route('purchase.index').'" class="nav-item active"', $html);

        // purchase-ledger must NOT have active class
        $this->assertStringNotContainsString(route('purchase.ledger').'" class="nav-item active"', $html);
        $this->assertStringContainsString(route('purchase.ledger').'" class="nav-item "', $html);
    }

    public function test_visiting_sales_ledger_page_only_activates_sales_ledger_in_sidebar(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('sales.ledger'));

        $response->assertOk();
        $response->assertSee(route('sales.ledger').'" class="nav-item active"', false);
        $response->assertDontSee(route('sales.index').'" class="nav-item active"', false);
    }

    public function test_visiting_purchase_ledger_page_only_activates_purchase_ledger_in_sidebar(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('purchase.ledger'));

        $response->assertOk();
        $response->assertSee(route('purchase.ledger').'" class="nav-item active"', false);
        $response->assertDontSee(route('purchase.index').'" class="nav-item active"', false);
    }
}
