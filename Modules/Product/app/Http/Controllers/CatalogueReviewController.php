<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Product\Models\Product;
use Modules\Product\Services\CatalogueService;

/**
 * Super-admin review of products that companies suggested for the shared
 * catalogue.
 */
class CatalogueReviewController extends Controller
{
    public function __construct(private CatalogueService $catalogue) {}

    public function index(): View
    {
        $suggestions = Product::query()
            ->whereNotNull('suggested_at')
            ->whereNotNull('company_id')
            ->with(['category:id,name', 'brand:id,name'])
            ->latest('suggested_at')
            ->get();

        return view('product::catalogue.review', compact('suggestions'));
    }

    public function approve(Product $product): RedirectResponse
    {
        $this->catalogue->approve($product);

        return back()->with('status', "\"{$product->name}\" শেয়ার্ড ক্যাটালগে যোগ করা হয়েছে");
    }

    public function reject(Product $product): RedirectResponse
    {
        $this->catalogue->reject($product);

        return back()->with('status', 'প্রস্তাবটি বাতিল করা হয়েছে');
    }
}
