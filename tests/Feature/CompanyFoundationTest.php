<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Modules\Company\Models\Company;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Subscription;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanyFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();
    }

    public function test_creating_a_shop_without_a_company_creates_a_hidden_company_for_it(): void
    {
        $shop = Shop::create(['name' => 'Rahim Store', 'slug' => 'rahim-store', 'phone' => '01711000000', 'status' => 'active']);

        $this->assertNotNull($shop->company_id);
        $this->assertSame('Rahim Store', $shop->company->name);
        $this->assertSame('rahim-store', $shop->company->slug);
        $this->assertSame('01711000000', $shop->company->phone);
        $this->assertFalse($shop->company->hasMultipleShops());
    }

    public function test_company_slug_stays_unique_when_generated_from_a_taken_shop_slug(): void
    {
        Company::factory()->create(['slug' => 'dhaka-mart']);

        $shop = Shop::create(['name' => 'Dhaka Mart', 'slug' => 'dhaka-mart', 'status' => 'active']);

        $this->assertSame('dhaka-mart-2', $shop->company->slug);
    }

    public function test_shop_created_for_a_company_joins_that_company(): void
    {
        $company = Company::factory()->create();

        $first = Shop::create(['company_id' => $company->id, 'name' => 'Branch One', 'slug' => 'branch-one', 'status' => 'active']);
        $second = Shop::create(['company_id' => $company->id, 'name' => 'Branch Two', 'slug' => 'branch-two', 'status' => 'active']);

        $this->assertSame(1, Company::count());
        $this->assertTrue($first->company->is($second->company));
        $this->assertTrue($company->hasMultipleShops());
    }

    public function test_all_shops_of_a_company_share_its_subscription_and_features(): void
    {
        $company = Company::factory()->create();
        $first = Shop::create(['company_id' => $company->id, 'name' => 'Branch One', 'slug' => 'branch-one', 'status' => 'active']);
        $second = Shop::create(['company_id' => $company->id, 'name' => 'Branch Two', 'slug' => 'branch-two', 'status' => 'active']);

        $this->subscribeShopToFeatures($first, ['sales', 'purchase']);
        $second->clearSubscriptionCache();

        $this->assertDatabaseHas('subscriptions', [
            'subscribable_type' => Company::class,
            'subscribable_id' => $company->id,
        ]);
        $this->assertSame($first->subscription()->getKey(), $second->subscription()->getKey());
        $this->assertTrue($second->hasFeature('sales'));
        $this->assertFalse($second->hasFeature('warehouses'));
    }

    public function test_an_expired_company_trial_blocks_every_shop_of_the_company(): void
    {
        $company = Company::factory()->create();
        $first = Shop::create(['company_id' => $company->id, 'name' => 'Branch One', 'slug' => 'branch-one', 'status' => 'active']);
        $second = Shop::create(['company_id' => $company->id, 'name' => 'Branch Two', 'slug' => 'branch-two', 'status' => 'active']);
        $this->subscribeShopToFeatures($first, ['sales']);

        $company->subscriptions()->update(['status' => 'trialing', 'trial_ends_at' => now()->subDay()]);
        $staff = User::factory()->create(['shop_id' => $second->id]);

        $this->actingAs($staff)->get(route('dashboard'))->assertRedirect(route('subscription.show'));
    }

    public function test_suspending_the_company_subscription_blocks_every_shop_of_the_company(): void
    {
        $company = Company::factory()->create();
        $first = Shop::create(['company_id' => $company->id, 'name' => 'Branch One', 'slug' => 'branch-one', 'status' => 'active']);
        $second = Shop::create(['company_id' => $company->id, 'name' => 'Branch Two', 'slug' => 'branch-two', 'status' => 'active']);
        $this->subscribeShopToFeatures($first, ['sales']);

        $company->subscriptions()->update(['status' => 'suspended']);
        $staff = User::factory()->create(['shop_id' => $second->id]);

        $this->actingAs($staff)->get(route('dashboard'))->assertRedirect(route('subscription.show'));
    }

    public function test_an_active_subscription_takes_precedence_over_an_older_cancelled_one(): void
    {
        $shop = Shop::create(['name' => 'Renewed Store', 'slug' => 'renewed-store', 'status' => 'active']);
        $shop->company->subscriptions()->create([
            'plan_id' => Plan::where('slug', 'free')->value('id'),
            'status' => 'cancelled',
            'starts_at' => now()->subYear(),
            'ends_at' => now()->subMonth(),
        ]);
        $this->subscribeShopToFeatures($shop, ['sales']);

        $this->assertTrue($shop->billingSubscription()->isUsable());
        $this->assertNotSame(
            route('subscription.show'),
            $this->actingAs(User::factory()->create(['shop_id' => $shop->id]))->get(route('dashboard'))->headers->get('Location'),
        );
    }

    public function test_registration_creates_a_company_with_the_owner_and_subscribes_the_company(): void
    {
        Mail::fake();

        $this->post(route('register.store'), [
            'name' => 'Kamal Hossain',
            'phone' => '01812345678',
            'email' => 'kamal@shop.com',
            'username' => 'kamal_store',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'shop_name' => 'Kamal Super Shop',
            'shop_slug' => 'kamal-super-shop',
            'shop_phone' => '01812345678',
            'shop_address' => 'Dhanmondi, Dhaka',
            'currency_symbol' => '৳',
            'branch_name' => 'Main Branch',
            'warehouse_name' => 'Main Warehouse',
            'opening_cash_balance' => 0,
        ])->assertRedirect(route('verification.notice'));

        $shop = Shop::where('slug', 'kamal-super-shop')->firstOrFail();
        $owner = User::where('email', 'kamal@shop.com')->firstOrFail();

        $this->assertSame('Kamal Super Shop', $shop->company->name);
        $this->assertTrue($shop->company->is($owner->primaryCompany()));
        $this->assertTrue((bool) $shop->company->owners()->whereKey($owner->id)->exists());
        $this->assertDatabaseHas('subscriptions', [
            'subscribable_type' => Company::class,
            'subscribable_id' => $shop->company_id,
            'plan_id' => Plan::where('slug', 'free')->value('id'),
        ]);
    }

    public function test_new_shop_for_an_existing_owner_joins_the_owners_company_and_shares_its_subscription(): void
    {
        $existingShop = Shop::create(['name' => 'Karim Traders', 'slug' => 'karim-traders', 'status' => 'active']);
        $owner = User::factory()->create(['shop_id' => $existingShop->id]);
        $existingShop->company->users()->attach($owner->id, ['role' => 'Admin', 'is_owner' => true]);
        $existingShop->subscribe(Plan::where('slug', 'standard')->firstOrFail());

        $this->actingAs($this->createSuperAdmin())->post(route('shops.store'), [
            'name' => 'Karim Traders Chattogram',
            'slug' => 'karim-traders-ctg',
            'status' => 'active',
            'owner_type' => 'existing',
            'existing_user_id' => $owner->id,
            'plan_id' => Plan::where('slug', 'free')->value('id'),
        ])->assertRedirect(route('shops.index'));

        $newShop = Shop::where('slug', 'karim-traders-ctg')->firstOrFail();

        $this->assertSame($existingShop->company_id, $newShop->company_id);
        $this->assertSame(1, Subscription::where('subscribable_id', $existingShop->company_id)->count());
        $this->assertSame('standard', $newShop->subscription()->getPlan()->slug);
    }

    public function test_new_shop_for_a_new_owner_gets_its_own_subscribed_company(): void
    {
        $this->actingAs($this->createSuperAdmin())->post(route('shops.store'), [
            'name' => 'Grand Market',
            'slug' => 'grand-market',
            'phone' => '01888999000',
            'status' => 'active',
            'admin_name' => 'Grand Manager',
            'admin_phone' => '01888999001',
            'admin_password' => 'Secret12345!',
            'admin_password_confirmation' => 'Secret12345!',
            'plan_id' => Plan::where('slug', 'standard')->value('id'),
        ])->assertRedirect(route('shops.index'));

        $shop = Shop::where('slug', 'grand-market')->firstOrFail();
        $manager = User::where('phone', '01888999001')->firstOrFail();

        $this->assertSame('Grand Market', $shop->company->name);
        $this->assertTrue((bool) $shop->company->owners()->whereKey($manager->id)->exists());
        $this->assertSame('standard', $shop->subscription()->getPlan()->slug);
    }

    public function test_company_scope_limits_records_to_the_current_company(): void
    {
        $record = $this->companyScopedModel();
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $shopA1 = Shop::create(['company_id' => $companyA->id, 'name' => 'A1', 'slug' => 'a1', 'status' => 'active']);
        $shopA2 = Shop::create(['company_id' => $companyA->id, 'name' => 'A2', 'slug' => 'a2', 'status' => 'active']);
        $shopB = Shop::create(['company_id' => $companyB->id, 'name' => 'B1', 'slug' => 'b1', 'status' => 'active']);

        $this->actingAs(User::factory()->create(['shop_id' => $shopA1->id]));
        $record->newQuery()->create(['label' => 'created in A1']);

        $this->actingAs(User::factory()->create(['shop_id' => $shopB->id]));
        $record->newQuery()->create(['label' => 'created in B1']);
        $this->assertSame(['created in B1'], $record->newQuery()->pluck('label')->all());

        $this->actingAs(User::factory()->create(['shop_id' => $shopA2->id]));
        $this->assertSame(['created in A1'], $record->newQuery()->pluck('label')->all());

        $this->actingAs($this->createSuperAdmin());
        $this->assertSame(2, $record->newQuery()->count());
    }

    public function test_company_scope_shows_nothing_to_a_user_without_a_current_shop(): void
    {
        $record = $this->companyScopedModel();
        $shop = Shop::create(['name' => 'A1', 'slug' => 'a1', 'status' => 'active']);
        $record->newQuery()->create(['label' => 'visible to A1 only', 'company_id' => $shop->company_id]);

        $this->actingAs(User::factory()->create(['shop_id' => null]));

        $this->assertSame(0, $record->newQuery()->count());
    }

    public function test_switching_shop_switches_the_company_context(): void
    {
        $record = $this->companyScopedModel();
        $shopA = Shop::create(['name' => 'Shop A', 'slug' => 'shop-a', 'status' => 'active']);
        $shopB = Shop::create(['name' => 'Shop B', 'slug' => 'shop-b', 'status' => 'active']);
        $record->newQuery()->create(['label' => 'A', 'company_id' => $shopA->company_id]);
        $record->newQuery()->create(['label' => 'B', 'company_id' => $shopB->company_id]);

        $user = User::factory()->create(['shop_id' => $shopA->id]);
        $shopB->users()->attach($user->id, ['role' => 'Admin', 'is_owner' => false]);
        $this->actingAs($user);
        $this->assertSame(['A'], $record->newQuery()->pluck('label')->all());

        $user->switchShop($shopB);

        $this->assertSame(['B'], $record->newQuery()->pluck('label')->all());
    }

    public function test_subscription_resolves_its_company_and_the_companys_first_shop(): void
    {
        $company = Company::factory()->create();
        $first = Shop::create(['company_id' => $company->id, 'name' => 'Branch One', 'slug' => 'branch-one', 'status' => 'active']);
        Shop::create(['company_id' => $company->id, 'name' => 'Branch Two', 'slug' => 'branch-two', 'status' => 'active']);
        $subscription = $first->subscribe(Plan::where('slug', 'standard')->firstOrFail());

        $subscription = Subscription::with(['company', 'shop'])->findOrFail($subscription->getKey());

        $this->assertTrue($subscription->company->is($company));
        $this->assertTrue($subscription->shop->is($first));
    }

    private function createSuperAdmin(): User
    {
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    /**
     * A throwaway company-scoped model backed by a table created for the test.
     */
    private function companyScopedModel(): Model
    {
        Schema::create('company_scoped_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id');
            $table->string('label');
            $table->timestamps();
        });

        return new class extends Model
        {
            use BelongsToCompany;

            protected $table = 'company_scoped_records';

            protected $guarded = [];
        };
    }
}
