<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Product\DataTables\ModelsDataTable;
use Modules\Product\Http\Requests\StoreProductModelRequest;
use Modules\Product\Http\Requests\UpdateProductModelRequest;
use Modules\Product\Models\Brand;
use Modules\Product\Models\ProductModel;

class ModelController extends Controller
{
    public function index(ModelsDataTable $dataTable): mixed
    {
        $brands = Brand::orderBy('name')->get();

        return $dataTable->render('product::models.index', compact('brands'));
    }

    public function create(): View
    {
        $brands = Brand::orderBy('name')->get();

        return view('product::models.create', ['model' => new ProductModel, 'brands' => $brands]);
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
        if ($model->products()->exists()) {
            return redirect()->route('models.index')->with('status', 'এই মডেলে পণ্য যুক্ত আছে, মুছে ফেলা যাবে না');
        }

        $model->delete();

        return redirect()->route('models.index')->with('status', 'মডেল মুছে ফেলা হয়েছে');
    }
}
