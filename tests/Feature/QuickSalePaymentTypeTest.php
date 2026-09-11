<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Finance\Models\Account;
use Modules\Sales\Models\Sale;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuickSalePaymentTypeTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected Account $cashAccount;

    protected Account $bankAccount;

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
            'name' => 'Quick Sale Shop',
            'slug' => 'quick-sale-shop',
            'status' => 'active',
        ]);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan) {
            $this->shop->subscribe($standardPlan);
        }
        $this->subscribeShopToFeatures($this->shop, Features::keys());

        $this->user = User::create([
            'name' => 'Quick Sale Admin',
            'email' => 'admin@quicksale.test',
            'password' => bcrypt('password'),
            'shop_id' => $this->shop->id,
        ]);
        $this->user->syncRoles([$adminRole]);

        $this->cashAccount = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'Counter Cash',
            'type' => 'cash',
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_default' => true,
            'status' => 'active',
        ]);

        $this->bankAccount = Account::create([
            'shop_id' => $this->shop->id,
            'name' => 'City Bank',
            'type' => 'bank',
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_default' => false,
            'status' => 'active',
        ]);
    }

    public function test_quick_sale_with_cash_payment_type(): void
    {
        $response = $this->actingAs($this->user)->post(route('quick-sale.store'), [
            'payment_type' => 'cash',
            'amount' => 1200.50,
            'sale_date' => now()->toDateString(),
            'customer_name' => 'John Cash',
            'note' => 'Cash counter sale',
        ]);

        $response->assertRedirect(route('sales.index'));

        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(1200.50, (float) $sale->total);
        $this->assertEquals(1200.50, (float) $sale->paid_amount);
        $this->assertEquals(0, (float) $sale->due_amount);
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals('নগদ টাকা', $sale->payment_method);

        $this->assertCount(1, $sale->payments);
        $payment = $sale->payments->first();
        $this->assertEquals('cash', $payment->method);
        $this->assertEquals(1200.50, (float) $payment->amount);
        $this->assertEquals($this->cashAccount->id, $payment->account_id);
    }

    public function test_quick_sale_with_bank_payment_type(): void
    {
        $response = $this->actingAs($this->user)->post(route('quick-sale.store'), [
            'payment_type' => 'bank',
            'account_id' => $this->bankAccount->id,
            'amount' => 2500.00,
            'sale_date' => now()->toDateString(),
            'customer_name' => 'Alice Bank',
            'note' => 'Card / Bank sale',
        ]);

        $response->assertRedirect(route('sales.index'));

        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(2500.00, (float) $sale->total);
        $this->assertEquals(2500.00, (float) $sale->paid_amount);
        $this->assertEquals(0, (float) $sale->due_amount);
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals('ব্যাংক', $sale->payment_method);

        $this->assertCount(1, $sale->payments);
        $payment = $sale->payments->first();
        $this->assertEquals('bank', $payment->method);
        $this->assertEquals(2500.00, (float) $payment->amount);
        $this->assertEquals($this->bankAccount->id, $payment->account_id);
    }

    public function test_quick_sale_with_both_payment_type(): void
    {
        $response = $this->actingAs($this->user)->post(route('quick-sale.store'), [
            'payment_type' => 'both',
            'account_id' => $this->bankAccount->id,
            'cash_amount' => 700.00,
            'bank_amount' => 300.00,
            'sale_date' => now()->toDateString(),
            'customer_name' => 'Bob Split',
            'note' => 'Part cash part bank sale',
        ]);

        $response->assertRedirect(route('sales.index'));

        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(1000.00, (float) $sale->total);
        $this->assertEquals(1000.00, (float) $sale->paid_amount);
        $this->assertEquals(0, (float) $sale->due_amount);
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals('উভয় (ক্যাশ + ব্যাংক)', $sale->payment_method);

        $this->assertCount(2, $sale->payments);

        $cashPayment = $sale->payments->firstWhere('method', 'cash');
        $this->assertNotNull($cashPayment);
        $this->assertEquals(700.00, (float) $cashPayment->amount);
        $this->assertEquals($this->cashAccount->id, $cashPayment->account_id);

        $bankPayment = $sale->payments->firstWhere('method', 'bank');
        $this->assertNotNull($bankPayment);
        $this->assertEquals(300.00, (float) $bankPayment->amount);
        $this->assertEquals($this->bankAccount->id, $bankPayment->account_id);
    }

    public function test_quick_sale_backward_compatibility(): void
    {
        $response = $this->actingAs($this->user)->post(route('quick-sale.store'), [
            'amount' => 800.00,
            'payment_method' => 'নগদ টাকা',
            'customer_name' => 'Legacy Customer',
        ]);

        $response->assertRedirect(route('sales.index'));

        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(800.00, (float) $sale->total);
        $this->assertCount(1, $sale->payments);
        $this->assertEquals('cash', $sale->payments->first()->method);
    }

    public function test_quick_sale_both_requires_at_least_one_amount(): void
    {
        $response = $this->actingAs($this->user)->post(route('quick-sale.store'), [
            'payment_type' => 'both',
            'cash_amount' => 0,
            'bank_amount' => 0,
        ]);

        $response->assertSessionHasErrors(['amount', 'cash_amount']);
    }

    public function test_quick_sale_ajax_submission_returns_json_and_completes_sale(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('quick-sale.store'), [
            'payment_type' => 'cash',
            'amount' => 1500.00,
            'sale_date' => now()->toDateString(),
            'customer_name' => 'Walk In Customer',
            'note' => 'Ajax Quick Sale',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'sale' => [
                'id',
                'invoice_no',
                'total',
                'paid_amount',
                'payment_method',
                'customer_name',
                'print_url',
                'invoice_modal_url',
            ],
        ]);

        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(1500.00, (float) $sale->total);
        $this->assertEquals(1500.00, (float) $sale->paid_amount);
        $this->assertEquals('Walk In Customer', $sale->customer->name);
    }

    public function test_quick_sale_ajax_validation_error_returns_422_json(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('quick-sale.store'), [
            'amount' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    public function test_quick_sale_modal_is_rendered_in_global_layout(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('id="quickSaleModal"', false);
        $response->assertSee('id="quickSaleGlobalForm"', false);
        $response->assertSee('data-quick-sale-trigger="true"', false);
        $response->assertSee('Alt+Q', false);
    }

    public function test_quick_sale_create_page_renders_modal_and_auto_open(): void
    {
        $response = $this->actingAs($this->user)->get(route('quick-sale.create'));

        $response->assertOk();
        $response->assertSee('id="quickSaleModal"', false);
        $response->assertSee('openQuickSaleModal()', false);
    }
}
