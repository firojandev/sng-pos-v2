<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Product\Http\Requests\UpdateShopCategoriesRequest;
use Modules\Product\Http\Requests\UpdateShopPriceRequest;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\ShopProduct;
use Modules\Product\Services\CatalogueService;
use Modules\Shop\Models\Shop;

/**
 * The current shop's catalogue: the categories it sells, listing its
 * products that it doesn't sell yet, and its own prices.
 */
class CatalogueController extends Controller
{
    public function __construct(private CatalogueService $catalogue) {}

    public function index(): View
    {
        $shop = $this->currentShop();

        return view('product::catalogue.index', [
            'shop' => $shop,
            'categories' => Category::query()->whereNull('parent_id')->orderBy('name')->get(),
            'selectedCategoryIds' => $shop->categories()->pluck('categories.id')->all(),
            'addableProducts' => $this->catalogue->addableProducts($shop)->groupBy('category_id'),
        ]);
    }

    public function updateCategories(UpdateShopCategoriesRequest $request): RedirectResponse
    {
        $this->catalogue->syncShopCategories($this->currentShop(), $request->validated('category_ids') ?? []);

        return redirect()->route('catalogue.index')->with('status', 'দোকানের ক্যাটাগরি হালনাগাদ করা হয়েছে');
    }

    public function listProduct(Product $product): RedirectResponse
    {
        $product->listInShop($this->currentShop()->id);

        return back()->with('status', "\"{$product->name}\" দোকানে যোগ করা হয়েছে");
    }

    public function listCategory(Category $category): RedirectResponse
    {
        $count = $this->catalogue->listCategory($this->currentShop(), $category);

        return back()->with('status', "{$count}টি পণ্য দোকানে যোগ করা হয়েছে");
    }

    public function editShopPrice(Product $product): View
    {
        return view('product::catalogue.shop-price', [
            'product' => $product,
            'listing' => $this->listingOf($product),
        ]);
    }

    public function updateShopPrice(UpdateShopPriceRequest $request, Product $product): RedirectResponse
    {
        $this->catalogue->setShopOverrides($this->listingOf($product), $request->validated());

        return redirect()->route('products.index')->with('status', 'দোকানের নিজস্ব মূল্য হালনাগাদ করা হয়েছে');
    }

    /**
     * Existing products that look like the one being entered.
     */
    public function duplicates(Request $request): JsonResponse
    {
        $matches = $this->catalogue->findDuplicates(
            $request->query('name'),
            $request->query('barcode'),
            $request->integer('ignore_id') ?: null,
        );
        $shopId = $this->currentShop()->id;

        return response()->json([
            'matches' => $matches->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'listed' => $product->shopListings()->where('shop_id', $shopId)->exists(),
                'list_url' => route('catalogue.products.list', $product),
            ])->values(),
        ]);
    }

    private function listingOf(Product $product): ShopProduct
    {
        return ShopProduct::where('shop_id', $this->currentShop()->id)
            ->where('product_id', $product->id)
            ->firstOrFail();
    }

    private function currentShop(): Shop
    {
        $shop = auth()->user()?->shop;
        abort_unless($shop, 403, 'কোনো দোকান নির্বাচন করা নেই (No shop selected)।');

        return $shop;
    }
}
