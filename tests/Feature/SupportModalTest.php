<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupportModalTest extends TestCase
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
            'name' => 'Test Shop',
            'slug' => 'test-shop',
            'status' => 'active',
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }
        $this->subscribeShopToFeatures($this->shop, Features::keys());

        $this->user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
            'email_verified_at' => now(),
        ]);
        $this->user->syncRoles([$adminRole]);
    }

    public function test_support_modal_is_rendered_with_contact_details(): void
    {
        $response = $this->actingAs($this->user)->get(route('batches.index'));

        $response->assertOk();
        $response->assertSee('id="supportModal"', false);
        $response->assertSee('class="support-modal-trigger"', false);
        $response->assertSee('+880 1886 861430');
        $response->assertSee('support@softngear.com');
        $response->assertSee('Shop 407, 3rd Floor, Shwapnochura Plaza, Rajshahi');
    }
}
