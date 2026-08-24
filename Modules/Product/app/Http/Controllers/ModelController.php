<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Product\Http\Requests\StoreProductModelRequest;
use Modules\Product\Http\Requests\UpdateProductModelRequest;
use Modules\Product\Models\Brand;
use Modules\Product\Models\ProductModel;

class ModelController extends Controller
{
    public function index(): View
    {
        $models = ProductModel::with('brand')->latest()->paginate(10);

        return view('product::models.index', compact('models'));
    }

    public function create(): View
    {
        $brands = Brand::orderBy('name')->get();

        return view('product::models.create', ['model' => new ProductModel(), 'brands' => $brands]);
    }

    public function store(StoreProductModelRequest $request): RedirectResponse
    {
        ProductModel::create($request->validated());

        return redirect()->route('models.index')->with('status', 'মডেল সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(ProductModel $model): View
    {
        $brands = Brand::orderBy('name')->get();

        return view('product::models.edit', compact('model', 'brands'));
    }

    public function update(UpdateProductModelRequest $request, ProductModel $model): RedirectResponse
    {
        $model->update($request->validated());

        return redirect()->route('models.index')->with('status', 'মডেল হালনাগাদ করা হয়েছে');
    }

    public function destroy(ProductModel $model): RedirectResponse
    {
        $model->delete();

        return redirect()->route('models.index')->with('status', 'মডেল মুছে ফেলা হয়েছে');
    }
}
