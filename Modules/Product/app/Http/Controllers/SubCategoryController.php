<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Product\Http\Requests\StoreSubCategoryRequest;
use Modules\Product\Http\Requests\UpdateSubCategoryRequest;
use Modules\Product\Models\Category;
use Modules\Product\Models\SubCategory;

class SubCategoryController extends Controller
{
    public function index(): View
    {
        $subCategories = SubCategory::with('category')->withCount('products')->latest()->paginate(10);

        return view('product::sub-categories.index', compact('subCategories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('product::sub-categories.create', ['subCategory' => new SubCategory, 'categories' => $categories]);
    }

    public function store(StoreSubCategoryRequest $request): RedirectResponse
    {
        SubCategory::create($request->validated());

        return redirect()->route('sub-categories.index')->with('status', 'সাব-ক্যাটাগরি সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(SubCategory $subCategory): View
    {
        $categories = Category::orderBy('name')->get();

        return view('product::sub-categories.edit', compact('subCategory', 'categories'));
    }

    public function update(UpdateSubCategoryRequest $request, SubCategory $subCategory): RedirectResponse
    {
        $subCategory->update($request->validated());

        return redirect()->route('sub-categories.index')->with('status', 'সাব-ক্যাটাগরি হালনাগাদ করা হয়েছে');
    }

    public function destroy(SubCategory $subCategory): RedirectResponse
    {
        if ($subCategory->products()->exists()) {
            return redirect()->route('sub-categories.index')->with('status', 'এই সাব-ক্যাটাগরিতে পণ্য যুক্ত আছে, মুছে ফেলা যাবে না');
        }

        $subCategory->delete();

        return redirect()->route('sub-categories.index')->with('status', 'সাব-ক্যাটাগরি মুছে ফেলা হয়েছে');
    }
}
