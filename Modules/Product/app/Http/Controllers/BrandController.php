<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Product\Http\Requests\StoreBrandRequest;
use Modules\Product\Http\Requests\UpdateBrandRequest;
use Modules\Product\Models\Brand;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::withCount(['models', 'products'])->latest()->paginate(10);

        return view('product::brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('product::brands.create', ['brand' => new Brand()]);
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        Brand::create($request->validated());

        return redirect()->route('brands.index')->with('status', 'ব্র্যান্ড সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Brand $brand): View
    {
        return view('product::brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($request->validated());

        return redirect()->route('brands.index')->with('status', 'ব্র্যান্ড হালনাগাদ করা হয়েছে');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->models()->exists() || $brand->products()->exists()) {
            return redirect()->route('brands.index')->with('status', 'এই ব্র্যান্ডে মডেল/পণ্য যুক্ত আছে, মুছে ফেলা যাবে না');
        }

        $brand->delete();

        return redirect()->route('brands.index')->with('status', 'ব্র্যান্ড মুছে ফেলা হয়েছে');
    }
}
