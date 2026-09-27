<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\LoyaltyPointTransaction;
use Modules\Customer\Models\LoyaltyProgram;
use Modules\Customer\Services\LoyaltyService;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleReturn;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoyaltyPointsTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private Warehouse $warehouse;

    private User $admin;

    private Customer $member;

    private LoyaltyService $loyalty;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create(['name' => 'Karim Supershop', 'slug' => 'karim-supershop', 'status' => 'active']);
        $this->subscribeShopToFeatures($this->shop, Features::keys());
        $branch = Branch::create(['shop_id' => $this->shop->id, 'name' => 'Main', 'status' => 'active']);
        $this->warehouse = Warehouse::create(['shop_id' => $this->shop->id, 'branch_id' => $branch->id, 'name' => 'Main Store', 'status' => 'active', 'is_default' => true]);

        $this->admin = User::factory()->create(['shop_id' => $this->shop->id]);
        $this->admin->syncRoles(['Admin']);
        $this->actingAs($this->admin);

        LoyaltyProgram::create([
            'company_id' => $this->shop->company_id,
            'is_enabled' => true,
            'spend_amount' => 100,
            'points_per_spend' => 2,
            'point_value' => 1,
            'points_expire_after_days' => 30,
        ]);

        $this->loyalty = app(LoyaltyService::class);
        $this->member = Customer::create(['name' => 'Rahim', 'phone' => '01711000000', 'status' => 'active']);
        $this->loyalty->enroll($this->member);
    }

    public function test_the_admin_sets_the_rules_and_the_shops_participation(): void
    {
        $this->put(route('loyalty.program.update'), [
            'is_enabled' => 1,
            'shop_participates' => 0,
            'spend_amount' => 200,
            'points_per_spend' => 5,
            'point_value' => 0.5,
            'min_redeem_points' => 50,
            'max_redeem_percent' => 30,
            'points_expire_after_days' => '',
        ])->assertRedirect(route('loyalty.index'));

        $program = LoyaltyProgram::forCompany($this->shop->company_id);
        $this->assertSame(5, $program->points_per_spend);
        $this->assertNull($program->points_expire_after_days, 'Empty expiry means points never expire.');
        $this->assertFalse($this->shop->fresh()->loyalty_enabled);
    }

    public function test_customers_join_for_free_with_a_unique_card_number(): void
    {
        $karim = Customer::create(['name' => 'Karim', 'status' => 'active']);
        $hasan = Customer::create(['name' => 'Hasan', 'status' => 'active']);

        $this->post(route('loyalty.members.store'), ['customer_id' => $karim->id, 'card_no' => 'CARD-1'])->assertRedirect(route('loyalty.index'));
        $this->post(route('loyalty.members.store'), ['customer_id' => $hasan->id, 'card_no' => 'CARD-1'])->assertSessionHasErrors('card_no');
        $this->post(route('loyalty.members.store'), ['customer_id' => $hasan->id])->assertRedirect(route('loyalty.index'));

        $this->assertTrue($karim->fresh()->isLoyaltyMember());
        $this->assertSame('M'.str_pad((string) $hasan->id, 6, '0', STR_PAD_LEFT), $hasan->fresh()->membership->card_no);
        $this->get(route('loyalty.index'))->assertOk()->assertSee('CARD-1')->assertSee('Rahim');
    }

    public function test_a_members_sale_earns_points_excluding_delivery(): void
    {
        $product = $this->productWithStock();

        $this->post(route('sales.store'), [
            'customer_id' => $this->member->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'delivery_charge' => 60,
            'items' => [['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 100, 'discount' => 0]],
            'payments' => [['method' => 'cash', 'amount' => 560]],
        ])->assertRedirect(route('sales.index'));

        // (560 - 60 delivery) / 100 = 5 x 2 points
        $this->assertSame(10, $this->loyalty->balance($this->member));
        $earning = LoyaltyPointTransaction::where('customer_id', $this->member->id)->firstOrFail();
        $this->assertSame(now()->addDays(30)->toDateString(), $earning->expires_at->toDateString());
    }

    public function test_non_members_and_shops_that_opted_out_earn_nothing(): void
    {
        $walkIn = Customer::create(['name' => 'Walk-in Regular', 'status' => 'active']);
        $this->loyalty->syncSale($this->saleFor($walkIn, 1000));
        $this->assertSame(0, $this->loyalty->balance($walkIn));

        $this->shop->update(['loyalty_enabled' => false]);
        $this->loyalty->syncSale($this->saleFor($this->member, 1000));
        $this->assertSame(0, $this->loyalty->balance($this->member));
    }

    public function test_points_follow_edits_returns_customer_changes_and_deletion(): void
    {
        $sale = $this->saleFor($this->member, 1000);
        $this->loyalty->syncSale($sale);
        $this->assertSame(20, $this->loyalty->balance($this->member));

        $this->loyalty->syncSale($sale);
        $this->assertSame(20, $this->loyalty->balance($this->member), 'Re-syncing does not earn twice.');

        $sale->update(['total' => 700]);
        $this->loyalty->syncSale($sale);
        $this->assertSame(14, $this->loyalty->balance($this->member));

        SaleReturn::create(['shop_id' => $this->shop->id, 'sale_id' => $sale->id, 'return_date' => now()->toDateString(), 'subtotal' => 200, 'refund_amount' => 0]);
        $this->loyalty->syncSale($sale);
        $this->assertSame(10, $this->loyalty->balance($this->member));

        $other = Customer::create(['name' => 'Karim', 'status' => 'active']);
        $this->loyalty->enroll($other);
        $sale->update(['customer_id' => $other->id]);
        $this->loyalty->syncSale($sale);
        $this->assertSame(0, $this->loyalty->balance($this->member));
        $this->assertSame(10, $this->loyalty->balance($other));

        $sale->delete();
        $this->loyalty->syncSale($sale);
        $this->assertSame(0, $this->loyalty->balance($other));
    }

    public function test_unspent_points_expire_after_their_date(): void
    {
        $this->loyalty->syncSale($this->saleFor($this->member, 500));
        LoyaltyPointTransaction::where('customer_id', $this->member->id)->update(['expires_at' => now()->subDay()->toDateString()]);

        $this->artisan('loyalty:expire-points')->expectsOutput('Expired 10 loyalty points.')->assertSuccessful();

        $this->assertSame(0, $this->loyalty->balance($this->member));
        $this->artisan('loyalty:expire-points')->expectsOutput('Expired 0 loyalty points.');
    }

    public function test_a_member_pays_part_of_the_bill_with_points(): void
    {
        $this->givePoints($this->member, 100);
        $product = $this->productWithStock();

        $this->post(route('sales.store'), [
            'customer_id' => $this->member->id,
            'warehouse_id' => $this->warehouse->id,
            'sale_date' => now()->toDateString(),
            'loyalty_points' => 50,
            'items' => [['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 100, 'discount' => 0]],
            'payments' => [['method' => 'cash', 'amount' => 450]],
        ])->assertRedirect(route('sales.index'));

        $sale = Sale::latest('id')->firstOrFail();
        $this->assertSame('450.00', $sale->total);
        $this->assertSame(50, $sale->loyalty_points_redeemed);
        $this->assertSame('50.00', $sale->loyalty_discount);
        // 5 x (100 - 60 cost) = 200, less the 50 points discount
        $this->assertSame(150.0, (float) $sale->profit);
        // 100 - 50 redeemed + floor(450 / 100) x 2 earned
        $this->assertSame(58, $this->loyalty->balance($this->member));
    }

    public function test_redemption_follows_the_programme_rules(): void
    {
        LoyaltyProgram::forCompany($this->shop->company_id)->update(['min_redeem_points' => 20, 'max_redeem_percent' => 10]);
        $this->givePoints($this->member, 100);
        $walkIn = Customer::create(['name' => 'Walk-in Regular', 'status' => 'active']);
        $shop = $this->shop->fresh();

        $cases = [
            'more than the balance' => [$this->member, 150],
            'below the minimum' => [$this->member, 10],
            'over 10% of the bill' => [$this->member, 60],
            'not a member' => [$walkIn, 20],
        ];

        foreach ($cases as $case => [$customer, $points]) {
            try {
                $this->loyalty->redemptionDiscount($customer, $points, 500, $shop);
                $this->fail("Redeeming {$case} should be refused.");
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('loyalty_points', $exception->errors(), $case);
            }
        }

        $this->assertSame(50.0, $this->loyalty->redemptionDiscount($this->member, 50, 500, $shop));
    }

    public function test_redeemed_points_come_back_when_the_sale_changes_or_is_deleted(): void
    {
        $this->givePoints($this->member, 100);
        LoyaltyProgram::forCompany($this->shop->company_id)->update(['is_enabled' => false]);
        $sale = $this->saleFor($this->member, 500);
        $sale->update(['loyalty_points_redeemed' => 60, 'loyalty_discount' => 60]);

        $this->loyalty->syncSale($sale);
        $this->assertSame(40, $this->loyalty->balance($this->member));

        $sale->update(['loyalty_points_redeemed' => 20]);
        $this->loyalty->syncSale($sale);
        $this->assertSame(80, $this->loyalty->balance($this->member));

        $sale->delete();
        $this->loyalty->syncSale($sale);
        $this->assertSame(100, $this->loyalty->balance($this->member));
    }

    public function test_the_oldest_expiring_points_are_spent_first(): void
    {
        $later = $this->givePoints($this->member, 30, now()->addDays(20));
        $sooner = $this->givePoints($this->member, 30, now()->addDays(5));
        $sale = $this->saleFor($this->member, 500);
        $sale->update(['loyalty_points_redeemed' => 40]);

        $this->loyalty->syncSale($sale);

        $this->assertSame(0, $sooner->fresh()->remaining);
        $this->assertSame(20, $later->fresh()->remaining);
    }

    public function test_the_counter_sees_how_many_points_a_member_can_use(): void
    {
        $this->givePoints($this->member, 75);

        $this->getJson(route('loyalty.customers.show', $this->member))
            ->assertOk()
            ->assertJson(['active' => true, 'member' => true, 'balance' => 75, 'available' => 75, 'point_value' => 1]);

        $this->get(route('sales.create'))->assertOk()->assertSee('loyalty-points-input', false);
    }

    private function givePoints(Customer $customer, int $points, ?Carbon $expiresAt = null): LoyaltyPointTransaction
    {
        return LoyaltyPointTransaction::create([
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'type' => LoyaltyPointTransaction::ADJUST,
            'points' => $points,
            'remaining' => $points,
            'expires_at' => $expiresAt?->toDateString(),
        ]);
    }

    private function saleFor(Customer $customer, float $total): Sale
    {
        return Sale::create([
            'shop_id' => $this->shop->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $customer->id,
            'invoice_no' => 'INV-'.uniqid(),
            'sale_date' => now()->toDateString(),
            'subtotal' => $total,
            'total' => $total,
            'paid_amount' => $total,
            'due_amount' => 0,
            'payment_status' => 'paid',
        ]);
    }

    private function productWithStock(): Product
    {
        $category = Category::create(['shop_id' => $this->shop->id, 'name' => 'Grocery', 'type' => 'product']);
        $product = Product::create(['shop_id' => $this->shop->id, 'name' => 'Rice', 'category_id' => $category->id, 'purchase_price' => 60, 'sale_price' => 100, 'status' => 'active']);
        Batch::create(['shop_id' => $this->shop->id, 'warehouse_id' => $this->warehouse->id, 'product_id' => $product->id, 'batch_no' => 'B-1', 'quantity' => 50, 'unit_cost' => 60]);

        return $product;
    }
}
