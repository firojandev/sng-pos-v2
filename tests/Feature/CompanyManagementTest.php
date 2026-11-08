<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Company\Models\Company;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();
    }

    public function test_super_admin_can_list_companies(): void
    {
        Shop::create(['name' => 'Rahim Store', 'slug' => 'rahim-store', 'status' => 'active']);
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)->get(route('companies.index'))
            ->assertOk()
            ->assertSee('companies-data-table');

        $this->actingAs($superAdmin)
            ->getJson(route('companies.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertSee('Default Company')
            ->assertDontSee('rahim-store', false);
    }

    public function test_shop_users_cannot_manage_companies(): void
    {
        $shop = Shop::create(['name' => 'Rahim Store', 'slug' => 'rahim-store', 'status' => 'active']);
        $owner = User::factory()->create(['shop_id' => $shop->id]);

        $this->actingAs($owner)->get(route('companies.index'))->assertForbidden();
        $this->actingAs($owner)->put(route('shops.company.update', $shop), ['company_id' => $shop->company_id])->assertForbidden();
    }

    public function test_super_admin_can_update_a_company(): void
    {
        $shop = Shop::create(['name' => 'Rahim Store', 'slug' => 'rahim-store', 'status' => 'active']);

        $this->actingAs($this->createSuperAdmin())->put(route('companies.update', $shop->company), [
            'name' => 'Rahim Group',
            'legal_name' => 'Rahim Group Ltd.',
            'tin' => '123456789012',
            'fiscal_year_start_month' => 1,
            'currency' => 'bdt',
            'status' => 'inactive',
        ])->assertRedirect(route('companies.edit', $shop->company));

        $this->assertDatabaseHas('companies', [
            'id' => $shop->company_id,
            'name' => 'Rahim Group',
            'legal_name' => 'Rahim Group Ltd.',
            'fiscal_year_start_month' => 1,
            'currency' => 'BDT',
            'status' => 'inactive',
        ]);
    }

    public function test_company_edit_page_lists_the_companys_shops(): void
    {
        $company = Company::factory()->create();
        Shop::create(['company_id' => $company->id, 'name' => 'Dhaka Outlet', 'slug' => 'dhaka-outlet', 'status' => 'active']);
        Shop::create(['company_id' => $company->id, 'name' => 'Ctg Outlet', 'slug' => 'ctg-outlet', 'status' => 'active']);

        $this->actingAs($this->createSuperAdmin())->get(route('companies.edit', $company))
            ->assertOk()
            ->assertSee('Dhaka Outlet')
            ->assertSee('Ctg Outlet');
    }

    public function test_moving_a_shop_into_an_owners_other_company_merges_the_companies(): void
    {
        $mainShop = Shop::create(['name' => 'Karim Traders', 'slug' => 'karim-traders', 'status' => 'active']);
        $mainShop->company->update(['type' => Company::TYPE_COMPANY, 'parent_id' => null]);
        $secondShop = Shop::create(['name' => 'Karim Traders Ctg', 'slug' => 'karim-traders-ctg', 'status' => 'active']);
        $owner = User::factory()->create(['shop_id' => $secondShop->id]);
        $mainShop->subscribe(Plan::where('slug', 'standard')->firstOrFail());
        $secondShop->subscribe(Plan::where('slug', 'free')->firstOrFail());
        $retiredCompanyId = $secondShop->company_id;

        $this->actingAs($this->createSuperAdmin())
            ->get(route('shops.edit', $secondShop))
            ->assertOk()
            ->assertSee('Move to Company');

        $this->put(route('shops.company.update', $secondShop), ['company_id' => $mainShop->company_id])
            ->assertRedirect(route('shops.edit', $secondShop));

        $secondShop->refresh();
        $this->assertSame($mainShop->company_id, $secondShop->company_id);
        $this->assertSoftDeleted('companies', ['id' => $retiredCompanyId]);
        $this->assertTrue((bool) $mainShop->company->owners()->whereKey($owner->id)->exists());
        $this->assertSame('standard', $secondShop->subscription()->getPlan()->slug);
    }

    public function test_a_company_without_billing_takes_over_the_retired_companys_subscription(): void
    {
        $target = Company::factory()->create();
        Shop::create(['company_id' => $target->id, 'name' => 'New Branch', 'slug' => 'new-branch', 'status' => 'active']);
        $shop = Shop::create(['name' => 'Old Store', 'slug' => 'old-store', 'status' => 'active']);
        $shop->subscribe(Plan::where('slug', 'standard')->firstOrFail());

        $this->actingAs($this->createSuperAdmin())
            ->put(route('shops.company.update', $shop), ['company_id' => $target->id])
            ->assertRedirect();

        $this->assertSame('standard', $target->fresh()->subscription()->getPlan()->slug);
    }

    public function test_a_shop_cannot_be_moved_into_its_own_company(): void
    {
        $shop = Shop::create(['name' => 'Rahim Store', 'slug' => 'rahim-store', 'status' => 'active']);

        $this->actingAs($this->createSuperAdmin())
            ->put(route('shops.company.update', $shop), ['company_id' => $shop->company_id])
            ->assertSessionHasErrors('company_id');
    }

    public function test_only_the_company_owner_or_admin_manages_the_company(): void
    {
        $shop = Shop::create(['name' => 'Rahim Store', 'slug' => 'rahim-store', 'status' => 'active']);
        $shop->company->update(['type' => Company::TYPE_COMPANY, 'parent_id' => null]);
        $owner = User::factory()->create(['shop_id' => $shop->id]);
        $shop->company->users()->attach($owner->id, ['role' => 'Owner', 'is_owner' => true]);
        $staff = User::factory()->create(['shop_id' => $shop->id]);

        $this->actingAs($owner)->get(route('company-settings.edit'))->assertOk()->assertSee('Rahim Store');
        $this->actingAs($owner)->get(route('dashboard'))->assertOk()->assertSee(route('company-settings.edit'));

        $this->actingAs($staff)->get(route('company-settings.edit'))->assertForbidden();
        $this->actingAs($staff)->get(route('dashboard'))->assertOk()->assertDontSee(route('company-settings.edit'));
    }

    public function test_multi_shop_company_admin_can_manage_company_settings(): void
    {
        [$company, $dhaka] = $this->multiShopCompany();
        $owner = User::factory()->create(['shop_id' => $dhaka->id]);
        $company->users()->attach($owner->id, ['role' => 'Owner', 'is_owner' => true]);

        $this->actingAs($owner)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('company-settings.edit'))
            ->assertSee($company->name.' › Dhaka Outlet');

        $this->actingAs($owner)->get(route('company-settings.edit'))
            ->assertOk()
            ->assertSee('Ctg Outlet');

        $this->actingAs($owner)->put(route('company-settings.update'), [
            'name' => 'Karim Group',
            'bin' => '000123456-0101',
            'fiscal_year_start_month' => 7,
            'currency' => 'BDT',
            'status' => 'inactive',
        ])->assertRedirect(route('company-settings.edit'));

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Karim Group',
            'bin' => '000123456-0101',
            'status' => 'active',
        ]);
    }

    public function test_staff_cannot_manage_company_settings(): void
    {
        [, $dhaka] = $this->multiShopCompany();
        $staff = User::factory()->create(['shop_id' => $dhaka->id]);
        $staff->shops()->updateExistingPivot($dhaka->id, ['role' => 'Cashier', 'is_owner' => false]);

        $this->actingAs($staff)->get(route('company-settings.edit'))->assertForbidden();
    }

    public function test_shop_picker_groups_shops_by_company_when_a_company_has_several_shops(): void
    {
        [$company, $dhaka, $ctg] = $this->multiShopCompany();
        $other = Shop::create(['name' => 'Separate Store', 'slug' => 'separate-store', 'status' => 'active']);
        $owner = User::factory()->create(['shop_id' => $dhaka->id]);
        $ctg->users()->attach($owner->id, ['role' => 'Admin', 'is_owner' => true]);
        $other->users()->attach($owner->id, ['role' => 'Admin', 'is_owner' => true]);

        $this->actingAs($owner)->get(route('shops.select'))
            ->assertOk()
            ->assertSee('shop-select-company', false)
            ->assertSeeInOrder([$company->name, 'Dhaka Outlet', 'Ctg Outlet', 'Separate Store']);
    }

    public function test_shop_picker_stays_a_plain_list_for_single_shop_companies(): void
    {
        $first = Shop::create(['name' => 'First Store', 'slug' => 'first-store', 'status' => 'active']);
        $second = Shop::create(['name' => 'Second Store', 'slug' => 'second-store', 'status' => 'active']);
        $owner = User::factory()->create(['shop_id' => $first->id]);
        $second->users()->attach($owner->id, ['role' => 'Admin', 'is_owner' => true]);

        $this->actingAs($owner)->get(route('shops.select'))
            ->assertOk()
            ->assertDontSee('class="shop-select-company"', false)
            ->assertSee('First Store')
            ->assertSee('Second Store');
    }

    /**
     * @return array{0: Company, 1: Shop, 2: Shop}
     */
    private function multiShopCompany(): array
    {
        $company = Company::factory()->create(['name' => 'Karim Holdings']);
        $dhaka = Shop::create(['company_id' => $company->id, 'name' => 'Dhaka Outlet', 'slug' => 'dhaka-outlet', 'status' => 'active']);
        $ctg = Shop::create(['company_id' => $company->id, 'name' => 'Ctg Outlet', 'slug' => 'ctg-outlet', 'status' => 'active']);

        return [$company, $dhaka, $ctg];
    }

    private function createSuperAdmin(): User
    {
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }
}
