<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Subscription;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShopSubscriptionStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    }

    public function test_subscription_status_labels_have_no_duplicates(): void
    {
        $labels = Subscription::statusLabels();

        $this->assertArrayHasKey('trialing', $labels);
        $this->assertArrayNotHasKey('trial', $labels);

        $bnLabels = array_column($labels, 'bn');
        $this->assertCount(count($bnLabels), array_unique($bnLabels));
    }

    public function test_backward_compatibility_for_trial_string_in_status_label(): void
    {
        $subscription = new Subscription;
        $subscription->status = 'trial';

        $label = $subscription->statusLabel();
        $this->assertEquals('ট্রায়াল', $label['bn']);
        $this->assertEquals('Trial', $label['en']);
    }

    public function test_subscription_status_validation_error_does_not_affect_shop_status(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $shop = Shop::create([
            'name' => 'Test Electronics',
            'slug' => 'test-electronics',
            'status' => 'active',
        ]);

        $plan = Plan::first();

        // Submit invalid subscription status
        $response = $this->actingAs($superAdmin)->from(route('shops.edit', $shop))->put(route('shops.subscription.update', $shop), [
            'plan_id' => $plan->id,
            'subscription_status' => 'invalid_status_value',
        ]);

        $response->assertRedirect(route('shops.edit', $shop));
        $response->assertSessionHasErrors('subscription_status');
        $response->assertSessionDoesntHaveErrors('status');

        $editPage = $this->actingAs($superAdmin)->get(route('shops.edit', $shop));
        $editPage->assertStatus(200);
        $editPage->assertSee('name="subscription_status"', false);
        $editPage->assertSee('name="status"', false);
    }

    public function test_can_update_subscription_with_subscription_status(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $shop = Shop::create([
            'name' => 'Gadget Zone',
            'slug' => 'gadget-zone',
            'status' => 'active',
        ]);

        $plan = Plan::first();

        $response = $this->actingAs($superAdmin)->put(route('shops.subscription.update', $shop), [
            'plan_id' => $plan->id,
            'subscription_status' => 'trialing',
            'current_period_start' => now()->toDateString(),
            'current_period_end' => now()->addDays(14)->toDateString(),
        ]);

        $response->assertRedirect(route('shops.edit', $shop));
        $response->assertSessionHasNoErrors();

        $subscription = $shop->fresh()->subscription();
        $this->assertNotNull($subscription);
        $statusValue = $subscription->status instanceof \BackedEnum ? $subscription->status->value : $subscription->status;
        $this->assertEquals('trialing', $statusValue);
    }

    public function test_can_update_subscription_with_legacy_status_field(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $shop = Shop::create([
            'name' => 'Legacy Shop',
            'slug' => 'legacy-shop',
            'status' => 'active',
        ]);

        $plan = Plan::first();

        $response = $this->actingAs($superAdmin)->put(route('shops.subscription.update', $shop), [
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('shops.edit', $shop));
        $response->assertSessionHasNoErrors();

        $subscription = $shop->fresh()->subscription();
        $this->assertNotNull($subscription);
        $statusValue = $subscription->status instanceof \BackedEnum ? $subscription->status->value : $subscription->status;
        $this->assertEquals('active', $statusValue);
    }

    public function test_shop_create_page_dates_have_no_hardcoded_defaults(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $response = $this->actingAs($superAdmin)->get(route('shops.create'));
        $response->assertStatus(200);

        // Verify that start and end date inputs are empty initially
        $today = date('Y-m-d');
        $thirtyDays = date('Y-m-d', strtotime('+30 days'));

        $response->assertDontSee('id="subscription-start-input" name="current_period_start" value="'.$today.'"', false);
        $response->assertDontSee('id="subscription-end-input" name="current_period_end" value="'.$thirtyDays.'"', false);
    }

    public function test_shop_create_with_existing_owner_succeeds_without_new_owner_fields(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $existingOwner = User::factory()->create([
            'phone' => '01711223344',
        ]);

        $plan = Plan::first();

        $response = $this->actingAs($superAdmin)->post(route('shops.store'), [
            'name' => 'Existing Owner Shop',
            'slug' => 'existing-owner-shop',
            'phone' => '01711223344', // shop phone matching owner phone
            'status' => 'active',
            'owner_type' => 'existing',
            'existing_user_id' => $existingOwner->id,
            'plan_id' => $plan->id,
            'subscription_status' => 'active',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('shops.index'));

        $shop = Shop::where('slug', 'existing-owner-shop')->first();
        $this->assertNotNull($shop);
        $this->assertTrue($shop->users->contains($existingOwner));

        // Verify the role assignment in model_has_roles has the new shop's id
        $modelRole = DB::table('model_has_roles')
            ->where('model_id', $existingOwner->id)
            ->where('shop_id', $shop->id)
            ->first();
        $this->assertNotNull($modelRole, 'Existing owner role was not assigned with the new shop_id in model_has_roles');

        // Switch to the new shop and verify permission
        $existingOwner->switchShop($shop);
        $this->actingAs($existingOwner);
        $this->assertTrue($existingOwner->can('sales.view'));

        // Verify sidebar renders features for the user
        $sidebarHtml = Blade::render('<x-core::sidebar />');
        $this->assertTrue(str_contains($sidebarHtml, 'sales.index') || str_contains($sidebarHtml, 'বিক্রয়'));
    }

    public function test_shop_edit_form_renders_start_and_end_dates(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $shop = Shop::create([
            'name' => 'Date Check Shop',
            'slug' => 'date-check-shop',
            'status' => 'active',
        ]);

        $plan = Plan::first();

        Subscription::create([
            'subscribable_type' => Shop::class,
            'subscribable_id' => $shop->id,
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => '2026-09-09 00:00:00',
            'ends_at' => '2026-10-09 00:00:00',
            'current_period_start' => '2026-09-09',
            'current_period_end' => '2026-10-09',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('shops.edit', $shop));
        $response->assertOk();
        $response->assertSee('value="2026-09-09"', false);
        $response->assertSee('value="2026-10-09"', false);
    }
}
