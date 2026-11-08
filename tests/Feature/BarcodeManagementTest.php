<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Services\BarcodeService;
use Modules\Shop\Models\Shop;
use PHPUnit\Framework\Attributes\TestWith;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BarcodeManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithPermissions(bool $canEdit = true): User
    {
        $shop = Shop::create(['name' => 'Barcode Shop', 'slug' => 'barcode-shop', 'status' => 'active']);
        $this->subscribeShopToFeatures($shop, ['products']);
        $user = User::factory()->create(['shop_id' => $shop->id]);
        setPermissionsTeamId($shop->id);
        foreach ($canEdit ? ['products.view', 'products.edit'] : ['products.view'] as $name) {
            $user->givePermissionTo(Permission::findOrCreate($name, 'web'));
        }
        $this->actingAs($user);

        return $user;
    }

    private function product(User $user, array $attributes = []): Product
    {
        $category = Category::create(['shop_id' => $user->shop_id, 'name' => 'Dates']);

        return Product::create([
            'shop_id' => $user->shop_id, 'name' => 'Dabbas Dates', 'category_id' => $category->id,
            'sale_price' => 100, ...$attributes,
        ]);
    }

    private function settings(array $ids): array
    {
        return ['product_ids' => $ids, 'format' => 'CODE128', 'width' => 50, 'height' => 30,
            'barcode_height' => 12, 'copies' => 1, 'paper' => 'a4', 'show_name' => 1, 'show_number' => 1];
    }

    public function test_guests_cannot_access_barcodes(): void
    {
        $this->get(route('barcodes.index'))->assertRedirect(route('login'));
    }

    public function test_index_search_and_preselection_render_without_other_shops(): void
    {
        $user = $this->userWithPermissions();
        $product = $this->product($user, ['sku' => 'DATES-KG']);
        $other = Shop::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active']);
        $this->product($user, ['shop_id' => $other->id, 'name' => 'Private dates']);

        $this->get(route('barcodes.index', ['search' => 'DATES-KG', 'product_id' => $product->id]))
            ->assertSee('Dabbas Dates')->assertDontSee('Private dates')->assertSee('checked', false);
    }

    public function test_generation_creates_unique_ean_compatible_numbers_and_reuses_them(): void
    {
        $user = $this->userWithPermissions();
        $first = $this->product($user);
        $second = $this->product($user);
        $data = [...$this->settings([$first->id, $second->id]), 'format' => 'EAN13'];

        $this->post(route('barcodes.store'), $data)->assertSee('Barcode labels');
        $number = $first->fresh()->barcode;
        $this->assertMatchesRegularExpression('/^2[0-9]{12}$/', $number);
        $this->assertTrue(app(BarcodeService::class)->supports($number, 'EAN13'));
        $this->assertNotSame($number, $second->fresh()->barcode);
        $this->assertDatabaseHas('products', ['id' => $first->id, 'has_barcode' => true]);

        $this->post(route('barcodes.store'), $data)->assertSee($number);
        $this->assertSame($number, $first->fresh()->barcode);
    }

    public function test_regeneration_explicitly_replaces_an_existing_number(): void
    {
        $user = $this->userWithPermissions();
        $product = $this->product($user, ['barcode' => 'OLD-123', 'has_barcode' => true]);

        $this->post(route('barcodes.store'), [...$this->settings([$product->id]), 'regenerate' => 1])->assertSee('Barcode labels');

        $this->assertNotSame('OLD-123', $product->fresh()->barcode);
    }

    public function test_viewer_can_reuse_but_cannot_create_or_regenerate_barcodes(): void
    {
        $user = $this->userWithPermissions(false);
        $product = $this->product($user, ['barcode' => 'EXISTING', 'has_barcode' => true]);
        $missing = $this->product($user);

        $this->post(route('barcodes.store'), $this->settings([$product->id]))->assertSee('EXISTING');
        $this->postJson(route('barcodes.store'), $this->settings([$missing->id]))->assertForbidden();
        $this->postJson(route('barcodes.store'), [...$this->settings([$product->id]), 'regenerate' => 1])->assertForbidden();
        $this->assertNull($missing->fresh()->barcode);
        $this->assertSame('EXISTING', $product->fresh()->barcode);
    }

    public function test_cross_shop_selection_is_rejected_without_partial_writes(): void
    {
        $user = $this->userWithPermissions();
        $own = $this->product($user);
        $other = Shop::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active']);
        $foreign = $this->product($user, ['shop_id' => $other->id]);

        $this->postJson(route('barcodes.store'), $this->settings([$own->id, $foreign->id]))->assertUnprocessable()->assertJsonValidationErrors('product_ids');

        $this->assertNull($own->fresh()->barcode);
        $this->assertDatabaseHas('products', ['id' => $foreign->id, 'barcode' => null]);
    }

    public function test_incompatible_ean_rolls_back_the_entire_batch(): void
    {
        $user = $this->userWithPermissions();
        $missing = $this->product($user);
        $existing = $this->product($user, ['barcode' => 'ABC-123']);

        $this->postJson(route('barcodes.store'), [...$this->settings([$missing->id, $existing->id]), 'format' => 'EAN13'])
            ->assertUnprocessable()->assertJsonValidationErrors('format');

        $this->assertNull($missing->fresh()->barcode);
        $this->assertSame('ABC-123', $existing->fresh()->barcode);
    }

    public function test_select_all_uses_filters_and_creates_the_requested_number_of_labels(): void
    {
        $user = $this->userWithPermissions();
        $match = $this->product($user, ['sku' => 'MATCH']);
        $excluded = $this->product($user, ['sku' => 'EXCLUDED']);
        $data = [...$this->settings([]), 'all_filtered' => 1, 'search' => 'MATCH', 'copies' => 3, 'paper' => 'thermal'];

        $response = $this->post(route('barcodes.store'), $data)->assertSee('Barcode labels');

        $this->assertSame(3, substr_count($response->getContent(), 'class="barcode-label"'));
        $this->assertNotNull($match->fresh()->barcode);
        $this->assertNull($excluded->fresh()->barcode);
    }

    public function test_invalid_dimensions_and_excessive_label_counts_do_not_write(): void
    {
        $user = $this->userWithPermissions();
        $product = $this->product($user);
        $data = $this->settings([$product->id]);

        $this->postJson(route('barcodes.store'), [...$data, 'width' => 0, 'copies' => 101])->assertUnprocessable()->assertJsonValidationErrors(['width', 'copies']);
        $this->assertNull($product->fresh()->barcode);
    }

    public function test_product_details_show_the_shared_barcode_entry_point(): void
    {
        $user = $this->userWithPermissions();
        $product = $this->product($user, ['barcode' => 'DETAIL-123']);

        $this->get(route('products.show', $product))->assertSee('DETAIL-123')
            ->assertSee(route('barcodes.index', ['product_id' => $product->id]), false);
        $this->get(route('categories.index'))->assertSee('Dates');
    }

    public function test_user_without_view_permission_cannot_access_the_feature(): void
    {
        $user = $this->userWithPermissions(false);
        $user->revokePermissionTo('products.view');

        $this->getJson(route('barcodes.index'))->assertForbidden();
        $this->postJson(route('barcodes.store'), $this->settings([]))->assertForbidden();
    }

    public function test_known_ean_checksum_is_validated_without_replacing_existing_codes(): void
    {
        $user = $this->userWithPermissions();
        $valid = $this->product($user, ['barcode' => '4006381333931']);
        $invalid = $this->product($user, ['barcode' => '4006381333932']);

        $this->post(route('barcodes.store'), [...$this->settings([$valid->id]), 'format' => 'EAN13'])->assertSee('4006381333931');
        $this->postJson(route('barcodes.store'), [...$this->settings([$invalid->id]), 'format' => 'EAN13'])
            ->assertUnprocessable()->assertJsonValidationErrors('format');
        $this->assertSame('4006381333932', $invalid->fresh()->barcode);
    }

    public function test_a4_pagination_has_no_extra_empty_page(): void
    {
        $user = $this->userWithPermissions();
        $product = $this->product($user, ['barcode' => 'PAGE-123']);

        $response = $this->post(route('barcodes.store'), [...$this->settings([$product->id]), 'copies' => 12, 'width' => 100, 'height' => 100]);

        $response->assertSee('Barcode labels');
        $this->assertSame(3, substr_count($response->getContent(), 'class="label-page"'));
        $this->assertSame(12, substr_count($response->getContent(), 'class="barcode-label"'));
    }

    public function test_a4_labels_are_paginated_inside_the_sheet_margins(): void
    {
        $user = $this->userWithPermissions();
        $product = $this->product($user, ['barcode' => 'MARGIN-123']);

        $response = $this->post(route('barcodes.store'), [...$this->settings([$product->id]), 'copies' => 19, 'width' => 100, 'height' => 29]);

        $response->assertViewHas('pages', fn ($pages) => $pages->count() === 2
            && $pages->first()->count() === 18 && $pages->last()->count() === 1);
    }

    public function test_each_product_uses_its_own_label_quantity(): void
    {
        $user = $this->userWithPermissions();
        $first = $this->product($user, ['barcode' => 'FIRST']);
        $second = $this->product($user, ['barcode' => 'SECOND']);
        $data = $this->settings([$first->id, $second->id]);
        unset($data['copies']);
        $data['quantities'] = [$first->id => 2, $second->id => 5];

        $response = $this->post(route('barcodes.store'), $data)->assertSee('Barcode labels');

        $this->assertSame(2, substr_count($response->getContent(), 'data-product="'.$first->id.'"'));
        $this->assertSame(5, substr_count($response->getContent(), 'data-product="'.$second->id.'"'));
        $this->assertSame('FIRST', $first->fresh()->barcode);
        $this->assertSame('SECOND', $second->fresh()->barcode);
    }

    #[TestWith([0])]
    #[TestWith([-1])]
    #[TestWith([101])]
    #[TestWith([1.5])]
    public function test_invalid_row_quantities_are_rejected_before_generation(int|float $quantity): void
    {
        $user = $this->userWithPermissions();
        $product = $this->product($user);
        $data = [...$this->settings([$product->id]), 'quantities' => [$product->id => $quantity]];

        $this->postJson(route('barcodes.store'), $data)->assertUnprocessable()
            ->assertJsonValidationErrors('quantities.'.$product->id);

        $this->assertNull($product->fresh()->barcode);
    }

    public function test_row_quantities_cannot_exceed_the_total_label_limit(): void
    {
        $user = $this->userWithPermissions();
        $ids = [];
        for ($i = 0; $i < 11; $i++) {
            $ids[] = $this->product($user)->id;
        }
        $data = [...$this->settings($ids), 'quantities' => array_fill_keys($ids, 100)];

        $this->postJson(route('barcodes.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('product_ids');

        $this->assertSame(0, Product::whereNotNull('barcode')->count());
    }

    public function test_product_names_are_escaped_in_preview(): void
    {
        $user = $this->userWithPermissions();
        $product = $this->product($user, ['name' => '<script>bad()</script>', 'barcode' => 'SAFE']);

        $this->post(route('barcodes.store'), $this->settings([$product->id]))
            ->assertSee('&lt;script&gt;bad()&lt;/script&gt;', false)->assertDontSee('<script>bad()</script>', false);
    }
}
