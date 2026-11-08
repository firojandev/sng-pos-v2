<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Product\Http\Requests\GenerateBarcodeRequest;
use Modules\Product\Models\Category;
use Modules\Product\Services\BarcodeService;

class BarcodeController extends Controller
{
    public function index(Request $request, BarcodeService $barcodes): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
        ]);

        return view('product::barcodes.index', [
            'products' => $barcodes->products($filters)->with('category')->orderBy('name')->orderBy('id')->paginate(30)->withQueryString(),
            'categories' => Category::orderBy('name')->pluck('name', 'id')->all(),
            'filters' => $filters,
        ]);
    }

    public function store(GenerateBarcodeRequest $request, BarcodeService $barcodes): View
    {
        $settings = $request->validated();
        $products = $barcodes->generate($settings);
        $sheetMargin = $settings['paper'] === 'thermal' ? 0 : 5;
        $columns = $settings['paper'] === 'thermal' ? 1 : (int) floor((210 - 2 * $sheetMargin) / $settings['width']);
        $rows = $settings['paper'] === 'thermal' ? 1 : (int) floor((297 - 2 * $sheetMargin) / $settings['height']);
        $horizontalMargin = $settings['paper'] === 'thermal' ? 0 : (210 - $columns * $settings['width']) / 2;
        $labels = $products->flatMap(fn ($product) => array_fill(0, $barcodes->labelQuantity($settings, $product->id), $product));

        return view('product::barcodes.preview', [
            'settings' => $settings,
            'pages' => $labels->chunk($columns * $rows),
            'columns' => $columns,
            'sheetMargin' => $sheetMargin,
            'horizontalMargin' => $horizontalMargin,
        ]);
    }
}
