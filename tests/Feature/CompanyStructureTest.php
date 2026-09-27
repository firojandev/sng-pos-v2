<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Company\Models\Company;
use Modules\Core\Support\Features;
use Modules\Finance\Models\Account;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanyStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();
    }

    public function test_a_super_admin_must_choose_or_create_the_company_first(): void
    {
        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)->get(route('shops.create'))->assertOk()->assertSee('New Company')->assertSee('Existing Company');

        $this->post(route('shops.store'), $this->shopPayload('No Company Shop') + ['company_mode' => 'existing'])->assertSessionHasErrors('company_id');
        $this->post(route('shops.store'), $this->shopPayload('No Company Shop') + ['company_mode' => 'new'])->assertSessionHasErrors('new_company_name');
        $this->assertSame(0, Shop::count());

        $this->post(route('shops.store'), $this->shopPayload('Karim Dhaka') + ['company_mode' => 'new', 'new_company_name' => 'Karim Group', 'plan_id' => Plan::where('slug', 'standard')->value('id')])
            ->assertRedirect(route('shops.index'));

        $dhaka = Shop::where('slug', 'karim-dhaka')->firstOrFail();
        $company = $dhaka->company;
        $owner = User::where('phone', '01711000101')->firstOrFail();
        $this->assertSame('Karim Group', $company->name);
        $this->assertTrue($owner->isShopAdmin($dhaka));
        $this->assertFalse($company->isAdministeredBy($owner), 'A shop admin is a shop-level (POS) login, not the company owner.');
        $this->assertTrue(Branch::withoutGlobalScopes()->where('shop_id', $dhaka->id)->exists());
        $this->assertTrue(Warehouse::withoutGlobalScopes()->where('shop_id', $dhaka->id)->exists());
        $this->assertTrue(Account::withoutGlobalScopes()->where('shop_id', $dhaka->id)->where('type', 'cash')->exists());

        $this->actingAs($superAdmin)->post(route('shops.store'), $this->shopPayload('Karim Ctg', '01711000102') + ['company_mode' => 'existing', 'company_id' => $company->id])
            ->assertRedirect(route('shops.index'));

        $ctg = Shop::where('slug', 'karim-ctg')->firstOrFail();
        $ctgAdmin = User::where('phone', '01711000102')->firstOrFail();
        $this->assertSame($company->id, $ctg->company_id);
        $this->assertTrue($ctgAdmin->isShopAdmin($ctg));
        $this->assertFalse($company->isAdministeredBy($ctgAdmin), 'A shop admin of an owned company is not a company admin.');
        $this->assertTrue($ctg->fresh()->hasFeature('sales'), 'The shop shares the company subscription.');
    }

    public function test_a_registering_shop_owner_gets_a_company_of_their_own(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Rahim', 'phone' => '01722000001', 'email' => 'rahim@example.com', 'password' => 'secret123', 'password_confirmation' => 'secret123',
            'shop_name' => 'Rahim Store', 'shop_slug' => 'rahim-store',
        ])->assertRedirect();

        $owner = User::where('email', 'rahim@example.com')->firstOrFail();
        $company = Shop::where('slug', 'rahim-store')->firstOrFail()->company;

        $this->assertTrue($company->isAdministeredBy($owner));
        $this->assertSame(1, $company->shops()->count());
    }

    public function test_the_company_owner_opens_shops_and_appoints_shop_admins(): void
    {
        [$company, $dhaka, $owner] = $this->companyWithOwner();

        $this->actingAs($owner)->get(route('company-settings.edit'))->assertOk()->assertSee('Open a New Shop')->assertSee('Add a Shop Admin');

        $this->post(route('company.shops.store'), ['name' => 'Karim Sylhet', 'phone' => '01733000000', 'opening_cash' => 5000])->assertRedirect()->assertSessionHasNoErrors();
        $sylhet = Shop::where('name', 'Karim Sylhet')->firstOrFail();
        $this->assertSame($company->id, $sylhet->company_id);
        $this->assertFalse(DB::table('shop_user')->where('shop_id', $sylhet->id)->where('user_id', $owner->id)->exists(), 'A company owner is not put into the shop: its POS is for shop admins.');
        $this->assertSame('5000.00', Account::withoutGlobalScopes()->where('shop_id', $sylhet->id)->where('type', 'cash')->value('current_balance'));
        $this->assertTrue($sylhet->hasFeature('sales'));

        $this->post(route('company.shop-admins.store'), [
            'admin_type' => 'new', 'shop_ids' => [$sylhet->id], 'name' => 'Sylhet Manager', 'phone' => '01733000001',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $manager = User::where('phone', '01733000001')->firstOrFail();
        $this->assertTrue($manager->isShopAdmin($sylhet));
        $this->assertFalse($manager->isShopAdmin($dhaka));
        $this->assertFalse($manager->isCompanyAdmin($company), 'A shop admin does not run the company.');

        $this->post(route('company.shop-admins.store'), ['admin_type' => 'existing', 'user_id' => $manager->id, 'shop_ids' => [$dhaka->id]])->assertSessionHasNoErrors();
        $this->assertTrue($manager->fresh()->isShopAdmin($dhaka));

        $this->delete(route('company.shop-admins.destroy', [$dhaka, $manager]))->assertRedirect();
        $this->assertFalse($manager->fresh()->belongsToShop($dhaka));
        $this->assertTrue($manager->fresh()->isShopAdmin($sylhet), 'Their other shop is kept.');

        $this->delete(route('company.shop-admins.destroy', [$dhaka, $owner]))->assertSessionHasErrors('admin');

        $this->actingAs($manager->fresh())->get(route('company-settings.edit'))->assertForbidden();
        $this->post(route('company.shops.store'), ['name' => 'Not Allowed'])->assertForbidden();
    }

    public function test_a_company_owner_cannot_touch_another_companys_shops(): void
    {
        [, , $owner] = $this->companyWithOwner();
        [, $otherShop] = $this->companyWithOwner('Other Group', 'other-shop', '01799000000');

        $this->actingAs($owner)->post(route('company.shop-admins.store'), ['admin_type' => 'existing', 'user_id' => $owner->id, 'shop_ids' => [$otherShop->id]])
            ->assertSessionHasErrors('shop_ids.0');
        $this->delete(route('company.shop-admins.destroy', [$otherShop, $owner]))->assertNotFound();
    }

    public function test_a_fresh_install_has_the_default_company_as_id_one(): void
    {
        $default = Company::defaultCompany();

        $this->assertSame(1, $default->id);
        $this->assertTrue($default->isDefault());
        $this->assertFalse($default->subscriptions()->exists(), 'The Default Company has no plan of its own.');
    }

    public function test_a_standalone_shop_has_its_own_plan_under_the_default_company(): void
    {
        $this->actingAs($this->superAdmin())->post(route('shops.store'), $this->shopPayload('Rahim Store') + [
            'company_mode' => 'standalone', 'plan_id' => Plan::where('slug', 'starter')->value('id'),
        ])->assertRedirect(route('shops.index'));
        $this->post(route('shops.store'), $this->shopPayload('Kamal Store', '01711000102') + [
            'company_mode' => 'standalone', 'plan_id' => Plan::where('slug', 'enterprise')->value('id'),
        ])->assertRedirect(route('shops.index'));

        $rahim = Shop::where('slug', 'rahim-store')->firstOrFail();
        $kamal = Shop::where('slug', 'kamal-store')->firstOrFail();

        $this->assertTrue($rahim->company->isStandalone());
        $this->assertSame(Company::defaultCompany()->id, (int) $rahim->company->parent_id);
        $this->assertNotSame($rahim->company_id, $kamal->company_id, 'Each standalone shop keeps its own data.');
        $this->assertSame('starter', $rahim->subscription()->getPlan()->slug);
        $this->assertSame('enterprise', $kamal->subscription()->getPlan()->slug, 'Each shop has its own plan.');
        $this->assertSame(Company::defaultCompany()->name, $rahim->company->displayName());

        $owner = User::where('phone', '01711000101')->firstOrFail();
        $this->assertTrue($owner->isShopAdmin($rahim));
        $this->assertFalse($owner->fresh()->isCompanyAdmin(), 'A shop owner logs in as a shop admin, not a company admin.');
    }

    public function test_a_company_created_with_a_plan_shares_it_with_all_its_shops(): void
    {
        $superAdmin = $this->superAdmin();
        $this->actingAs($superAdmin)->get(route('companies.create'))->assertOk();

        $this->post(route('companies.store'), [
            'name' => 'Karim Group', 'fiscal_year_start_month' => 7, 'currency' => 'BDT', 'plan_id' => Plan::where('slug', 'enterprise')->value('id'),
        ])->assertRedirect();
        $company = Company::where('name', 'Karim Group')->firstOrFail();
        $this->assertTrue($company->isBusiness());

        $this->post(route('shops.store'), $this->shopPayload('Karim Dhaka') + ['company_mode' => 'existing', 'company_id' => $company->id, 'plan_id' => Plan::where('slug', 'free')->value('id')]);
        $this->post(route('shops.store'), $this->shopPayload('Karim Ctg', '01711000102') + ['company_mode' => 'existing', 'company_id' => $company->id]);

        foreach (Shop::where('company_id', $company->id)->get() as $shop) {
            $this->assertSame('enterprise', $shop->subscription()->getPlan()->slug, 'The company plan, not the one picked on the shop form.');
        }
        $this->assertSame(1, $company->subscriptions()->count());
        $this->assertFalse($company->isAdministeredBy(User::where('phone', '01711000101')->firstOrFail()), 'Shop admins are shop-level logins.');
    }

    public function test_registration_as_a_company_or_as_a_shop_owner(): void
    {
        $register = fn (array $data) => $this->post(route('register.store'), $data + ['password' => 'secret123', 'password_confirmation' => 'secret123']);

        $register(['account_type' => 'company', 'company_name' => 'Karim Group', 'name' => 'Karim', 'phone' => '01722000001', 'email' => 'karim@example.com', 'shop_name' => 'Karim Dhaka', 'shop_slug' => 'karim-dhaka'])->assertRedirect();
        auth()->logout();
        $register(['account_type' => 'shop', 'name' => 'Rahim', 'phone' => '01722000002', 'email' => 'rahim@example.com', 'shop_name' => 'Rahim Store', 'shop_slug' => 'rahim-store'])->assertRedirect();

        $karimShop = Shop::where('slug', 'karim-dhaka')->firstOrFail();
        $this->assertTrue($karimShop->company->isBusiness());
        $this->assertSame('Karim Group', $karimShop->company->name);
        $this->assertTrue(User::where('email', 'karim@example.com')->firstOrFail()->isCompanyAdmin($karimShop->company));
        $this->assertTrue($karimShop->company->subscriptions()->exists(), 'The plan is on the company.');

        $rahimShop = Shop::where('slug', 'rahim-store')->firstOrFail();
        $this->assertTrue($rahimShop->company->isStandalone());
        $this->assertTrue($rahimShop->company->subscriptions()->exists(), 'The plan is the shop\'s own.');
        $this->assertFalse(User::where('email', 'rahim@example.com')->firstOrFail()->isCompanyAdmin($rahimShop->company));
    }

    public function test_the_default_company_admin_sees_and_opens_standalone_shops(): void
    {
        $standalone = Shop::create(['name' => 'Rahim Store', 'slug' => 'rahim-store', 'status' => 'active']);
        $this->subscribeShopToFeatures($standalone, Features::keys());
        [, $companyShop] = $this->companyWithOwner();

        $admin = User::factory()->create(['phone' => '01744000000']);
        $this->actingAs($this->superAdmin())->post(route('companies.admins.store', Company::defaultCompany()), ['phone' => '01744000000', 'role' => 'Admin'])->assertSessionHasNoErrors();
        $this->assertTrue($admin->fresh()->isDefaultCompanyAdmin());

        auth()->logout();
        $this->post(route('login.store'), ['login' => $admin->email, 'email' => $admin->email, 'password' => 'password'])->assertRedirect(route('default-company.index'));

        $this->actingAs($admin->fresh())->get(route('default-company.index'))->assertOk()->assertSee('Rahim Store')->assertDontSee('Karim Group Dhaka');
        $this->get(route('dashboard'))->assertRedirect(route('default-company.index'));

        $this->post(route('default-company.shops.open', $companyShop))->assertNotFound();
        $this->post(route('default-company.shops.open', $standalone))->assertRedirect(route('dashboard'));
        $this->assertSame($standalone->id, (int) $admin->fresh()->shop_id);
        $this->assertTrue($admin->fresh()->isShopAdmin($standalone));
        $this->actingAs($admin->fresh())->get(route('dashboard'))->assertOk();

        $stranger = User::factory()->create();
        $this->actingAs($stranger)->get(route('default-company.index'))->assertForbidden();
    }

    /**
     * @return array{0: Company, 1: Shop, 2: User}
     */
    private function companyWithOwner(string $companyName = 'Karim Group', string $slug = 'karim-dhaka', string $phone = '01711000001'): array
    {
        $company = Company::create(['name' => $companyName, 'slug' => Company::generateUniqueSlug($companyName), 'status' => 'active']);
        $shop = Shop::create(['company_id' => $company->id, 'name' => $companyName.' Dhaka', 'slug' => $slug, 'status' => 'active']);
        $this->subscribeShopToFeatures($shop, Features::keys());
        $owner = User::factory()->create(['shop_id' => $shop->id, 'phone' => $phone]);
        setPermissionsTeamId($shop->id);
        $owner->assignRole(Role::firstOrCreate(['shop_id' => $shop->id, 'name' => 'Admin', 'guard_name' => 'web']));
        setPermissionsTeamId(null);
        $shop->users()->syncWithoutDetaching([$owner->id => ['role' => 'Admin', 'is_owner' => true]]);
        $company->users()->attach($owner->id, ['role' => Company::ROLE_OWNER, 'is_owner' => true]);

        return [$company, $shop, $owner];
    }

    /**
     * @return array<string, mixed>
     */
    private function shopPayload(string $name, string $adminPhone = '01711000101'): array
    {
        return [
            'name' => $name, 'slug' => Str::slug($name), 'status' => 'active', 'owner_type' => 'new',
            'admin_name' => $name.' Admin', 'admin_phone' => $adminPhone, 'admin_password' => 'secret123', 'admin_password_confirmation' => 'secret123',
        ];
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']));

        return $user;
    }
}
