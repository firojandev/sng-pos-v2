<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Product\Http\Requests\MergeProductsRequest;
use Modules\Product\Models\Product;
use Modules\Product\Services\ProductMergeService;

/**
 * Super-admin tool for merging duplicate products.
 */
class CatalogueMergeController extends Controller
{
    public function index(): View
    {
        // Products sharing a name (ignoring case and spacing) are likely duplicates.
        $duplicateNames = Product::query()
            ->select(DB::raw('LOWER(TRIM(name)) as normalized_name'))
            ->groupBy('normalized_name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('normalized_name');

        $groups = Product::query()
            ->whereIn(DB::raw('LOWER(TRIM(name))'), $duplicateNames)
            ->with(['company:id,name', 'category:id,name'])
            ->withSum(['batches as stock_quantity' => fn ($batches) => $batches->withoutGlobalScope('shop')], 'quantity')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Product $product) => mb_strtolower(trim($product->name)));

        return view('product::catalogue.merge', compact('groups'));
    }

    public function merge(MergeProductsRequest $request, ProductMergeService $merger): RedirectResponse
    {
        $duplicate = Product::findOrFail($request->validated('duplicate_id'));
        $keep = Product::findOrFail($request->validated('keep_id'));

        $merger->merge($duplicate, $keep);

        return redirect()->route('catalogue-merge.index')
            ->with('status', "\"{$duplicate->name}\" (#{$duplicate->id}) পণ্যটি #{$keep->id}-এর সাথে মার্জ করা হয়েছে");
    }
}
