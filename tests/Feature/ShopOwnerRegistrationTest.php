<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Modules\Auth\Mail\NewShopAdminNotificationMail;
use Modules\Auth\Mail\ShopVerificationMail;
use Modules\Auth\Mail\WelcomeShopMail;
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
            'email' => '',
            'password' => 'secret',
            'password_confirmation' => 'mismatch',
            'shop_name' => '',
            'shop_slug' => '',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['name', 'phone', 'email', 'password', 'shop_name', 'shop_slug']);
    }

    public function test_successful_multi_step_registration_and_free_package_assignment(): void
    {
        Mail::fake();

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

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHas('status');

        Mail::assertSent(ShopVerificationMail::class, function ($mail) {
            return $mail->hasTo('kamal@shop.com');
        });

        // 1. Owner was created
        $user = User::where('phone', '01812345678')->first();
        $this->assertNotNull($user);
        $this->assertEquals('কামাল হোসেন', $user->name);
        $this->assertEquals('kamal_store', $user->username);
        $this->assertEquals('kamal@shop.com', $user->email);
        $this->assertNull($user->email_verified_at);
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
            'email' => 'test@blocked.com',
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

    public function test_unverified_shop_owner_cannot_access_dashboard_and_is_redirected_to_verification_notice(): void
    {
        $shop = Shop::create([
            'name' => 'Pending Verify Shop',
            'slug' => 'pending-verify-shop',
            'store_code' => 'PVS-01',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Unverified Owner',
            'phone' => '01700112233',
            'email' => 'unverified@shop.test',
            'password' => bcrypt('password123'),
            'shop_id' => $shop->id,
            'email_verified_at' => null,
        ]);

        $shop->users()->syncWithoutDetaching([
            $owner->id => ['role' => 'Admin', 'is_owner' => true],
        ]);

        // 1. Trying to visit dashboard redirects to verification.notice
        $response = $this->actingAs($owner)->get(route('dashboard'));
        $response->assertRedirect(route('verification.notice'));

        // 2. Verification notice renders successfully
        $noticeResponse = $this->actingAs($owner)->get(route('verification.notice'));
        $noticeResponse->assertStatus(200);
        $noticeResponse->assertSee('ইমেইল ভেরিফাই করুন');
        $noticeResponse->assertSee('unverified@shop.test');
        $noticeResponse->assertSee('পুনরায় ভেরিফিকেশন ইমেইল পাঠান');
    }

    public function test_email_verification_via_signed_url_sends_welcome_mail_and_notifies_admin(): void
    {
        Mail::fake();

        // Create Super Admin
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@softngear.com',
            'phone' => '01799887766',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($superAdminRole);

        $shop = Shop::create([
            'name' => 'Super Fresh Mart',
            'slug' => 'super-fresh-mart',
            'store_code' => 'SFM-99',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Mart Owner',
            'phone' => '01899112233',
            'email' => 'martowner@fresh.test',
            'password' => bcrypt('password123'),
            'shop_id' => $shop->id,
            'email_verified_at' => null,
        ]);

        $shop->users()->syncWithoutDetaching([
            $owner->id => ['role' => 'Admin', 'is_owner' => true],
        ]);

        $this->assertFalse($owner->hasVerifiedEmail());

        // Generate signed verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $owner->id,
                'hash' => sha1($owner->getEmailForVerification()),
            ]
        );

        // Access signed verification URL
        $response = $this->get($verificationUrl);

        $response->assertRedirect(route('verification.success'));
        $this->assertGuest();

        // Verify success page renders with login link
        $successResponse = $this->get(route('verification.success'));
        $successResponse->assertStatus(200);
        $successResponse->assertSee(route('login'));
        $successResponse->assertSee('ইমেইল ভেরিফিকেশন সম্পন্ন');
        $successResponse->assertSee('লগইন করুন');

        // Accessing the same verification link a second time shows expired
        $secondResponse = $this->get($verificationUrl);
        $secondResponse->assertStatus(200);
        $secondResponse->assertViewIs('auth::verify-expired');
        $secondResponse->assertSee('মেয়াদ শেষ');
        $this->assertGuest();

        // Refresh owner model
        $owner->refresh();
        $this->assertTrue($owner->hasVerifiedEmail());
        $this->assertNotNull($owner->email_verified_at);

        // 1. Welcome Mail sent to Shop Owner
        Mail::assertSent(WelcomeShopMail::class, function ($mail) {
            return $mail->hasTo('martowner@fresh.test');
        });

        // 2. Admin Notification Mail sent to Super Admins (hardcoded + database super admins)
        Mail::assertSent(NewShopAdminNotificationMail::class, function ($mail) {
            return $mail->hasTo('admin@sngpos.com');
        });

        Mail::assertSent(NewShopAdminNotificationMail::class, function ($mail) {
            return $mail->hasTo('softngear@gmail.com');
        });

        Mail::assertSent(NewShopAdminNotificationMail::class, function ($mail) {
            return $mail->hasTo('admin@softngear.com');
        });

        // 3. Now verified shop owner can login manually and access dashboard
        $loginResponse = $this->post(route('login.store'), [
            'login' => 'martowner@fresh.test',
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($owner);

        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
    }

    public function test_resend_verification_email(): void
    {
        Mail::fake();

        $shop = Shop::create([
            'name' => 'Resend Mart',
            'slug' => 'resend-mart',
            'store_code' => 'RM-01',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Resend User',
            'phone' => '01611223344',
            'email' => 'resend@mart.test',
            'password' => bcrypt('password123'),
            'shop_id' => $shop->id,
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($owner)->post(route('verification.send'));
        $response->assertRedirect();
        $response->assertSessionHas('status');

        Mail::assertSent(ShopVerificationMail::class, function ($mail) {
            return $mail->hasTo('resend@mart.test');
        });
    }

    public function test_unverified_owner_login_redirects_to_verification_notice(): void
    {
        $shop = Shop::create([
            'name' => 'Login Test Shop',
            'slug' => 'login-test-shop',
            'store_code' => 'LTS-01',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Login Owner',
            'phone' => '01755667788',
            'email' => 'loginowner@test.com',
            'password' => bcrypt('secret123'),
            'shop_id' => $shop->id,
            'email_verified_at' => null,
        ]);

        $shop->users()->syncWithoutDetaching([
            $owner->id => ['role' => 'Admin', 'is_owner' => true],
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'loginowner@test.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHas('warning');
    }

    public function test_tampered_verification_url_returns_forbidden(): void
    {
        $shop = Shop::create([
            'name' => 'Tamper Test Shop',
            'slug' => 'tamper-test-shop',
            'store_code' => 'TTS-01',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Tamper Owner',
            'phone' => '01733445566',
            'email' => 'tamper@test.com',
            'password' => bcrypt('secret123'),
            'shop_id' => $shop->id,
            'email_verified_at' => null,
        ]);

        // Wrong hash
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $owner->id,
                'hash' => 'invalid-tampered-hash',
            ]
        );

        $response = $this->get($url);
        $response->assertStatus(403);
    }

    public function test_already_verified_user_accessing_notice_redirects_to_dashboard(): void
    {
        $shop = Shop::create([
            'name' => 'Verified Shop',
            'slug' => 'verified-shop',
            'store_code' => 'VS-01',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Verified Owner',
            'phone' => '01711223399',
            'email' => 'alreadyverified@test.com',
            'password' => bcrypt('secret123'),
            'shop_id' => $shop->id,
            'email_verified_at' => now(),
        ]);

        $shop->users()->syncWithoutDetaching([
            $owner->id => ['role' => 'Admin', 'is_owner' => true],
        ]);

        $response = $this->actingAs($owner)->get(route('verification.notice'));
        $response->assertRedirect(route('dashboard'));
    }

    public function test_email_and_phone_required_validation_messages(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Name Only',
            'phone' => '',
            'email' => '',
            'password' => '123456',
            'password_confirmation' => '123456',
            'shop_name' => 'Test Shop',
            'shop_slug' => 'test-shop-slug',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'phone' => 'মোবাইল নম্বর প্রদান করা আবশ্যক।',
            'email' => 'ইমেইল ঠিকানা প্রদান করা আবশ্যক।',
        ]);
    }

    public function test_logged_in_user_is_logged_out_upon_email_verification(): void
    {
        Mail::fake();

        $shop = Shop::create([
            'name' => 'Logout Shop',
            'slug' => 'logout-shop',
            'store_code' => 'LS-01',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Logout Owner',
            'phone' => '01700112233',
            'email' => 'logoutowner@test.com',
            'password' => bcrypt('password123'),
            'shop_id' => $shop->id,
            'email_verified_at' => null,
        ]);

        $shop->users()->syncWithoutDetaching([
            $owner->id => ['role' => 'Admin', 'is_owner' => true],
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $owner->id,
                'hash' => sha1($owner->getEmailForVerification()),
            ]
        );

        // Access verification link while logged in
        $response = $this->actingAs($owner)->get($verificationUrl);

        $response->assertRedirect(route('verification.success'));
        $this->assertGuest();
        $this->assertTrue($owner->fresh()->hasVerifiedEmail());
    }

    public function test_expired_signature_renders_expired_page_with_forbidden_status(): void
    {
        $shop = Shop::create([
            'name' => 'Expired Shop',
            'slug' => 'expired-shop',
            'store_code' => 'ES-01',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Expired Owner',
            'phone' => '01700445566',
            'email' => 'expiredowner@test.com',
            'password' => bcrypt('password123'),
            'shop_id' => $shop->id,
            'email_verified_at' => null,
        ]);

        // URL expired in the past
        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinutes(5),
            [
                'id' => $owner->id,
                'hash' => sha1($owner->getEmailForVerification()),
            ]
        );

        $response = $this->get($expiredUrl);

        $response->assertStatus(403);
        $response->assertViewIs('auth::verify-expired');
        $response->assertSee('মেয়াদ শেষ');
    }
}
