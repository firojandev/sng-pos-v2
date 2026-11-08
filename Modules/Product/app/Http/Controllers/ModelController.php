<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Product\DataTables\ModelsDataTable;
use Modules\Product\Http\Controllers\Concerns\ChangesOwnCatalogueOnly;
use Modules\Product\Http\Requests\StoreProductModelRequest;
use Modules\Product\Http\Requests\UpdateProductModelRequest;
use Modules\Product\Models\Brand;
use Modules\Product\Models\ProductModel;

class ModelController extends Controller
{
    use ChangesOwnCatalogueOnly;

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
        $this->ensureOwnRecord($model);

        $brands = Brand::orderBy('name')->get();

        return view('product::models.edit', compact('model', 'brands'));
    }

    public function update(UpdateProductModelRequest $request, ProductModel $model): RedirectResponse
    {
        $this->ensureOwnRecord($model);

        $model->update($request->validated());

        return redirect()->route('models.index')->with('status', 'মডেল হালনাগাদ করা হয়েছে');
    }

    public function destroy(ProductModel $model): RedirectResponse
    {
        $this->ensureOwnRecord($model);

        $model->delete();

        return redirect()->route('models.index')->with('status', 'মডেল মুছে ফেলা হয়েছে');
    }
}
