<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Product\DataTables\SubCategoriesDataTable;
use Modules\Product\Http\Requests\StoreSubCategoryRequest;
use Modules\Product\Http\Requests\UpdateSubCategoryRequest;
use Modules\Product\Models\Category;
use Modules\Product\Models\SubCategory;

class SubCategoryController extends Controller
{
    public function index(SubCategoriesDataTable $dataTable): mixed
    {
        $categories = Category::parents()->orderBy('name')->get();

        return $dataTable->render('product::sub-categories.index', compact('categories'));
    }

    public function create(): View
    {
        $categories = Category::parents()->orderBy('name')->get();

        return view('product::sub-categories.create', ['subCategory' => new SubCategory, 'categories' => $categories]);
    }

    public function store(StoreSubCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (isset($data['category_id']) && ! isset($data['parent_id'])) {
            $data['parent_id'] = $data['category_id'];
        }

        SubCategory::create($data);

        return redirect()->route('sub-categories.index')->with('status', 'সাব-ক্যাটাগরি সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(SubCategory $subCategory): View
    {
        $categories = Category::parents()->orderBy('name')->get();

        return view('product::sub-categories.edit', compact('subCategory', 'categories'));
    }

    public function update(UpdateSubCategoryRequest $request, SubCategory $subCategory): RedirectResponse
    {
        $data = $request->validated();
        if (isset($data['category_id']) && ! isset($data['parent_id'])) {
            $data['parent_id'] = $data['category_id'];
        }

        $subCategory->update($data);

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
