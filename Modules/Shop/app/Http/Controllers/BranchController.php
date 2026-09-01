<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Shop\Http\Requests\StoreBranchRequest;
use Modules\Shop\Http\Requests\UpdateBranchRequest;
use Modules\Shop\Models\Branch;
use Modules\Shop\Support\PlanLimits;

class BranchController extends Controller
{
    public function index(): View
    {
        $branches = Branch::withCount('warehouses')->orderBy('name')->paginate(10);

        return view('shop::branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('shop::branches.create', ['branch' => new Branch]);
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id;

        if ($message = PlanLimits::check($shopId, 'max_branches', Branch::count())) {
            return redirect()->route('branches.index')->with('status', $message);
        }

        Branch::create($request->validated());

        return redirect()->route('branches.index')->with('status', 'শাখা সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Branch $branch): View
    {
        return view('shop::branches.edit', compact('branch'));
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($request->validated());

        return redirect()->route('branches.index')->with('status', 'শাখা হালনাগাদ করা হয়েছে');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        if ($branch->warehouses()->exists()) {
            return redirect()->route('branches.index')->with('status', 'এই শাখার গুদাম আছে, মুছে ফেলা যাবে না');
        }

        $branch->delete();

        return redirect()->route('branches.index')->with('status', 'শাখা মুছে ফেলা হয়েছে');
    }
}
