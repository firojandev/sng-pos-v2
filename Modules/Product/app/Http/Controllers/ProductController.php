<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Product\DataTables\ProductsDataTable;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use Modules\Product\Models\Brand;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\SubCategory;
use Modules\Product\Models\Unit;
use Modules\Shop\Support\PlanLimits;

class ProductController extends Controller
{
    public function index(ProductsDataTable $dataTable): mixed
    {
        $categories = Category::parents()->orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return $dataTable->render('product::products.index', compact('categories', 'brands'));
    }

    public function create(): View
    {
        return view('product::products.create', [
            'product' => new Product,
            ...$this->formOptions(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id;

        if ($message = PlanLimits::check($shopId, 'max_products', Product::count())) {
            return redirect()->route('products.index')->with('status', $message);
        }

        $data = $request->safe()->except(['image', 'units']);
        $data['is_vat'] = $request->boolean('is_vat');
        $data['has_warranty'] = $request->boolean('has_warranty');
        $data['has_expiry'] = $request->boolean('has_expiry');
        $data['is_wholesale'] = $request->boolean('is_wholesale');
        $data['has_discount'] = $request->boolean('has_discount');
        $data['has_barcode'] = $request->boolean('has_barcode');

        if ($request->hasFile('image')) {
            $data['image_url'] = Storage::disk('public')->url(
                $request->file('image')->store('products', 'public')
            );
        }

        DB::transaction(function () use ($data, $request) {
            $product = Product::create($data);
            $product->units()->sync($this->unitsPivotData($request->validated('units')));
        });

        return redirect()->route('products.index')->with('status', 'পণ্য সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Product $product): View
    {
        $product->load('units');

        return view('product::products.edit', [
            'product' => $product,
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'units']);
        $data['is_vat'] = $request->boolean('is_vat');
        $data['has_warranty'] = $request->boolean('has_warranty');
        $data['has_expiry'] = $request->boolean('has_expiry');
        $data['is_wholesale'] = $request->boolean('is_wholesale');
        $data['has_discount'] = $request->boolean('has_discount');
        $data['has_barcode'] = $request->boolean('has_barcode');

        if ($request->hasFile('image')) {
            if ($product->image_url) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $product->image_url));
            }

            $data['image_url'] = Storage::disk('public')->url(
                $request->file('image')->store('products', 'public')
            );
        }

        DB::transaction(function () use ($product, $data, $request) {
            $product->update($data);
            $product->units()->sync($this->unitsPivotData($request->validated('units')));
        });

        return redirect()->route('products.index')->with('status', 'পণ্য হালনাগাদ করা হয়েছে');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_url) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $product->image_url));
        }

        $product->delete();

        return redirect()->route('products.index')->with('status', 'পণ্য মুছে ফেলা হয়েছে');
    }

    public function stockHistory(Product $product): View
    {
        $movements = $product->stockMovements()
            ->with(['batch', 'creator', 'reference'])
            ->latest()
            ->paginate(15);

        $totalStock = (float) $product->batches()->sum('quantity');

        return view('product::products._stock_history_modal', compact('product', 'movements', 'totalStock'));
    }

    private function unitsPivotData(array $units): array
    {
        return collect($units)->mapWithKeys(fn (array $row) => [
            $row['unit_id'] => [
                'is_base' => (bool) ($row['is_base'] ?? false),
                'conversion_factor' => $row['conversion_factor'],
                'is_smaller_unit' => (bool) ($row['is_smaller_unit'] ?? false),
            ],
        ])->toArray();
    }

    private function formOptions(): array
    {
        return [
            'categories' => Category::parents()->with('subCategories')->orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
            'subCategories' => SubCategory::orderBy('name')->get(),
        ];
    }
}
