<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Setting;
use Modules\Finance\Models\Account;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Revoltify\Subscriptionify\Enums\SubscriptionStatus;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShopOwnerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SubscriptionifySeeder::class);
    }

    public function test_register_page_renders_successfully(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('নতুন দোকান রেজিস্টার করুন');
        $response->assertSee('Register Your Shop');
        $response->assertSee('আপনার তথ্য');
        $response->assertSee('দোকানের বিবরণ');
        $response->assertSee('সেটআপ ও প্ল্যান');
        $response->assertSee('name="name"', false);
        $response->assertSee('name="phone"', false);
        $response->assertSee('name="shop_name"', false);
        $response->assertSee('name="shop_slug"', false);
        $response->assertSee('ফ্রি প্ল্যান', false);
        $response->assertSee('jquery-3.7.1.min.js', false);
    }

    public function test_check_availability_endpoint(): void
    {
        Shop::create([
            'name' => 'Existing Store',
            'slug' => 'taken-slug',
            'store_code' => 'shop-999',
            'status' => 'active',
        ]);

        User::factory()->create([
            'phone' => '01711111111',
            'email' => 'taken@example.com',
            'username' => 'takenuser',
        ]);

        $response = $this->getJson(route('register.check-availability', [
            'slug' => 'taken-slug',
            'phone' => '01711111111',
            'email' => 'taken@example.com',
            'username' => 'takenuser',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'slug_available' => false,
            'phone_available' => false,
            'email_available' => false,
            'username_available' => false,
        ]);

        $availableResponse = $this->getJson(route('register.check-availability', [
            'slug' => 'fresh-slug',
            'phone' => '01999999999',
            'email' => 'fresh@example.com',
            'username' => 'freshuser',
        ]));

        $availableResponse->assertStatus(200);
        $availableResponse->assertJson([
            'slug_available' => true,
            'phone_available' => true,
            'email_available' => true,
            'username_available' => true,
        ]);
    }

    public function test_validation_errors_prevent_registration(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => '',
            'phone' => '',
            'password' => 'secret',
            'password_confirmation' => 'mismatch',
            'shop_name' => '',
            'shop_slug' => '',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['name', 'phone', 'password', 'shop_name', 'shop_slug']);
    }

    public function test_successful_multi_step_registration_and_free_package_assignment(): void
    {
        $freePlan = Plan::where('slug', 'free')->first();
        $this->assertNotNull($freePlan, 'Free plan should exist');

        $payload = [
            // Step 1: Owner Info
            'name' => 'কামাল হোসেন',
            'phone' => '01812345678',
            'email' => 'kamal@shop.com',
            'username' => 'kamal_store',
            'password' => 'password123',
            'password_confirmation' => 'password123',

            // Step 2: Shop Info
            'shop_name' => 'কামাল সুপার শপ',
            'shop_slug' => 'kamal-super-shop',
            'shop_phone' => '01812345678',
            'shop_address' => 'ধানমন্ডি ২৭, ঢাকা',
            'currency_symbol' => '৳',

            // Step 3: Setup & Initial Cash
            'branch_name' => 'ধানমন্ডি শাখা',
            'warehouse_name' => 'প্রধান গুদাম',
            'opening_cash_balance' => 5000,
        ];

        $response = $this->post(route('register.store'), $payload);

        $response->assertRedirect(route('dashboard'));

        // 1. Owner was created
        $user = User::where('phone', '01812345678')->first();
        $this->assertNotNull($user);
        $this->assertEquals('কামাল হোসেন', $user->name);
        $this->assertEquals('kamal_store', $user->username);
        $this->assertNotNull($user->support_pin);
        $this->assertEquals(6, strlen($user->support_pin));

        // 2. Shop was created
        $shop = Shop::where('slug', 'kamal-super-shop')->first();
        $this->assertNotNull($shop);
        $this->assertEquals('কামাল সুপার শপ', $shop->name);
        $this->assertNotNull($shop->store_code);
        $this->assertEquals('active', $shop->status);
        $this->assertEquals($shop->id, $user->shop_id);

        // 3. User authenticated
        $this->assertAuthenticatedAs($user);
        $this->assertEquals($shop->id, session('current_shop_id'));

        // 4. Role & Pivot assigned
        $this->assertTrue($user->isShopOwner($shop->id));
        $this->assertTrue($user->isShopAdmin($shop));

        setPermissionsTeamId($shop->id);
        $this->assertTrue($user->hasRole('Admin'));
        setPermissionsTeamId(null);

        // 5. Default Branch & Warehouse created
        $branch = Branch::where('shop_id', $shop->id)->first();
        $this->assertNotNull($branch);
        $this->assertEquals('ধানমন্ডি শাখা', $branch->name);

        $warehouse = Warehouse::where('shop_id', $shop->id)->first();
        $this->assertNotNull($warehouse);
        $this->assertEquals('প্রধান গুদাম', $warehouse->name);
        $this->assertEquals($branch->id, $warehouse->branch_id);
        $this->assertTrue((bool) $warehouse->is_default);

        // 6. Primary Cash Account & Opening Balance created
        $cashAccount = Account::withoutGlobalScopes()->where('shop_id', $shop->id)->where('type', 'cash')->first();
        $this->assertNotNull($cashAccount);
        $this->assertEquals(5000, (float) $cashAccount->current_balance);

        // 7. Free Plan Subscription auto-assigned
        $subscription = $shop->activeSubscription;
        $this->assertNotNull($subscription);
        $this->assertEquals($freePlan->id, $subscription->plan_id);
        $statusValue = $subscription->status instanceof SubscriptionStatus
            ? $subscription->status->value
            : (string) $subscription->status;
        $this->assertEquals('active', $statusValue);
        $this->assertNull($subscription->ends_at);
        $this->assertTrue($subscription->isUsable());
    }

    public function test_admin_can_toggle_registration_on_and_off(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($superAdminRole);

        // Turn OFF
        $response = $this->actingAs($admin)->postJson(route('system-settings.toggle-registration'), [
            'state' => 0,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'enabled' => false]);
        $this->assertFalse(Setting::isRegistrationEnabled());

        // Turn ON
        $response = $this->actingAs($admin)->postJson(route('system-settings.toggle-registration'), [
            'state' => 1,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'enabled' => true]);
        $this->assertTrue(Setting::isRegistrationEnabled());
    }

    public function test_registration_blocked_when_disabled(): void
    {
        Setting::setRegistrationEnabled(false);

        // 1. Visiting /register redirects to login
        $response = $this->get(route('register'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');

        // 2. Submitting registration is blocked
        $postResponse = $this->post(route('register.store'), [
            'name' => 'Test User',
            'phone' => '01912345678',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'shop_name' => 'Blocked Shop',
            'shop_slug' => 'blocked-shop',
        ]);
        $postResponse->assertRedirect(route('login'));
        $postResponse->assertSessionHas('error');
        $this->assertDatabaseMissing('users', ['phone' => '01912345678']);
        $this->assertDatabaseMissing('shops', ['slug' => 'blocked-shop']);

        // 3. Availability check returns 403
        $ajaxResponse = $this->getJson(route('register.check-availability', ['slug' => 'any-slug']));
        $ajaxResponse->assertStatus(403);

        // 4. Login page hides registration link
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertDontSee('route(\'register\')', false);
        $loginResponse->assertDontSee('ফ্রি অ্যাকাউন্ট তৈরি করুন');
    }
}
