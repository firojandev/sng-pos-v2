<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Product\Http\Requests\StoreCategoryRequest;
use Modules\Product\Http\Requests\UpdateCategoryRequest;
use Modules\Product\Models\Category;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount(['subCategories', 'products'])->latest()->paginate(10);

        return view('product::categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('product::categories.create', ['category' => new Category()]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('categories.index')->with('status', 'ক্যাটাগরি সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Category $category): View
    {
        return view('product::categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('status', 'ক্যাটাগরি হালনাগাদ করা হয়েছে');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->subCategories()->exists() || $category->products()->exists()) {
            return redirect()->route('categories.index')->with('status', 'এই ক্যাটাগরিতে সাব-ক্যাটাগরি/পণ্য যুক্ত আছে, মুছে ফেলা যাবে না');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('status', 'ক্যাটাগরি মুছে ফেলা হয়েছে');
    }
}
