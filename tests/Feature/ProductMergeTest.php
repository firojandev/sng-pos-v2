<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\ShopProduct;
use Modules\Product\Models\Unit;
use Modules\Sales\Models\Sale;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductMergeTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private Warehouse $warehouse;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shop = Shop::create(['name' => 'Rahim Store', 'slug' => 'rahim-store', 'status' => 'active']);
        $branch = Branch::create(['shop_id' => $this->shop->id, 'name' => 'Main', 'status' => 'active']);
        $this->warehouse = Warehouse::create(['shop_id' => $this->shop->id, 'branch_id' => $branch->id, 'name' => 'Main Store', 'status' => 'active']);
        $this->category = Category::create(['shop_id' => $this->shop->id, 'name' => 'Grocery', 'type' => 'product']);
    }

    public function test_merging_moves_every_record_to_the_kept_product(): void
    {
        $keep = $this->product('Miniket Rice');
        $duplicate = $this->product('miniket rice ', ['has_barcode' => true, 'barcode' => '8901234567890']);
        $piece = Unit::create(['shop_id' => $this->shop->id, 'name' => 'Piece', 'short_code' => 'pc']);
        $keep->units()->attach($piece->id, ['is_base' => true, 'conversion_factor' => 1]);
        $duplicate->units()->attach($piece->id, ['is_base' => true, 'conversion_factor' => 1]);
        $this->batch($keep, 'B-1', 10);
        $collidingBatch = $this->batch($duplicate, 'B-1', 4);
        $sale = Sale::create(['shop_id' => $this->shop->id, 'warehouse_id' => $this->warehouse->id, 'invoice_no' => 'INV-1', 'sale_date' => now()->toDateString(), 'subtotal' => 80, 'total' => 80, 'paid_amount' => 80, 'due_amount' => 0, 'payment_status' => 'paid']);
        $sale->items()->create(['product_id' => $duplicate->id, 'batch_id' => $collidingBatch->id, 'quantity' => 1, 'unit_price' => 80, 'total' => 80]);

        $this->actingAs($this->superAdmin())->post(route('catalogue-merge.merge'), [
            'duplicate_id' => $duplicate->id,
            'keep_id' => $keep->id,
        ])->assertRedirect(route('catalogue-merge.index'));

        $this->assertNull(Product::withoutGlobalScopes()->find($duplicate->id));
        $this->assertSame([$keep->id], DB::table('sale_items')->pluck('product_id')->all());
        $this->assertSame(['B-1', 'B-1-M'.$duplicate->id], Batch::withoutGlobalScopes()->where('product_id', $keep->id)->orderBy('id')->pluck('batch_no')->all());
        $this->assertSame(1, DB::table('product_units')->where('product_id', $keep->id)->count());
        $this->assertSame(1, ShopProduct::where('product_id', $keep->id)->count());
        $this->assertSame('8901234567890', Product::withoutGlobalScopes()->findOrFail($keep->id)->barcode);
    }

    public function test_a_company_product_can_be_folded_into_a_shared_one_but_not_the_other_way(): void
    {
        $shared = Product::create(['name' => 'Mineral Water', 'category_id' => $this->category->id]);
        $own = $this->product('Mineral Water');
        $otherOwn = $this->product('Mineral Water 2');
        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)->post(route('catalogue-merge.merge'), ['duplicate_id' => $shared->id, 'keep_id' => $own->id])
            ->assertSessionHasErrors('keep_id');

        $this->actingAs($superAdmin)->post(route('catalogue-merge.merge'), ['duplicate_id' => $own->id, 'keep_id' => $shared->id])
            ->assertSessionHasNoErrors();
        $this->assertNull(Product::withoutGlobalScopes()->find($own->id));

        $foreignShop = Shop::create(['name' => 'Other Store', 'slug' => 'other-store', 'status' => 'active']);
        $foreign = Product::create(['shop_id' => $foreignShop->id, 'name' => 'Their Water', 'category_id' => $this->category->id]);
        $this->actingAs($superAdmin)->post(route('catalogue-merge.merge'), ['duplicate_id' => $otherOwn->id, 'keep_id' => $foreign->id])
            ->assertSessionHasErrors('keep_id');
    }

    public function test_the_merge_page_groups_products_that_share_a_name(): void
    {
        $this->product('Miniket Rice');
        $this->product('MINIKET RICE');
        $this->product('Red Lentils');

        $this->actingAs($this->superAdmin())->get(route('catalogue-merge.index'))
            ->assertOk()
            ->assertSee('MINIKET RICE')
            ->assertDontSee('Red Lentils');
    }

    public function test_only_a_super_admin_can_merge(): void
    {
        $user = User::factory()->create(['shop_id' => $this->shop->id]);

        $this->actingAs($user)->get(route('catalogue-merge.index'))->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function product(string $name, array $attributes = []): Product
    {
        return Product::create(array_merge(['shop_id' => $this->shop->id, 'name' => $name, 'category_id' => $this->category->id], $attributes));
    }

    private function batch(Product $product, string $batchNo, float $quantity): Batch
    {
        return Batch::create(['shop_id' => $this->shop->id, 'warehouse_id' => $this->warehouse->id, 'product_id' => $product->id, 'batch_no' => $batchNo, 'quantity' => $quantity, 'unit_cost' => 50]);
    }

    private function superAdmin(): User
    {
        setPermissionsTeamId(null);
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['shop_id' => null]);
        $user->assignRole('Super Admin');

        return $user;
    }
}
