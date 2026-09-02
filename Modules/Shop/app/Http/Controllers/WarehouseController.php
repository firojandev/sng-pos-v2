<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Shop\DataTables\WarehousesDataTable;
use Modules\Shop\Http\Requests\StoreWarehouseRequest;
use Modules\Shop\Http\Requests\UpdateWarehouseRequest;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Warehouse;
use Modules\Shop\Support\PlanLimits;

class WarehouseController extends Controller
{
    public function index(WarehousesDataTable $dataTable): mixed
    {
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        $allBranches = Branch::orderBy('name')->get();

        $branchOptions = ['' => '-- শাখা নির্বাচন করুন (Select Branch) --'];
        foreach ($branches as $branch) {
            $branchOptions[$branch->id] = $branch->name;
        }

        $filterBranchOptions = [];
        foreach ($allBranches as $b) {
            $filterBranchOptions[$b->id] = $b->name;
        }

        return $dataTable->render('shop::warehouses.index', compact('branches', 'branchOptions', 'filterBranchOptions'));
    }

    public function create(): View
    {
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        $branchOptions = ['' => '-- শাখা নির্বাচন করুন (Select Branch) --'];
        foreach ($branches as $branch) {
            $branchOptions[$branch->id] = $branch->name;
        }

        return view('shop::warehouses.create', [
            'warehouse' => new Warehouse,
            'branches' => $branches,
            'branchOptions' => $branchOptions,
        ]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse|JsonResponse
    {
        $shopId = auth()->user()->shop_id;

        if ($message = PlanLimits::check($shopId, 'max_warehouses', Warehouse::count())) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('warehouses.index')->with('status', $message);
        }

        $warehouse = Warehouse::create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'গুদাম সফলভাবে যোগ করা হয়েছে',
                'warehouse' => $warehouse,
            ]);
        }

        return redirect()->route('warehouses.index')->with('status', 'গুদাম সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, Warehouse $warehouse): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $warehouse->id,
                'branch_id' => $warehouse->branch_id,
                'name' => $warehouse->name,
                'address' => $warehouse->address,
                'status' => $warehouse->status,
                'update_url' => route('warehouses.update', $warehouse),
            ]);
        }

        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        $branchOptions = ['' => '-- শাখা নির্বাচন করুন (Select Branch) --'];
        foreach ($branches as $branch) {
            $branchOptions[$branch->id] = $branch->name;
        }

        return view('shop::warehouses.edit', compact('warehouse', 'branches', 'branchOptions'));
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse|JsonResponse
    {
        $warehouse->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'গুদাম হালনাগাদ করা হয়েছে',
                'warehouse' => $warehouse,
            ]);
        }

        return redirect()->route('warehouses.index')->with('status', 'গুদাম হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, Warehouse $warehouse): RedirectResponse|JsonResponse
    {
        if ($warehouse->batches()->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'এই গুদামে পণ্যের মজুদ আছে, মুছে ফেলা যাবে না',
                ], 422);
            }

            return redirect()->route('warehouses.index')->with('status', 'এই গুদামে পণ্যের মজুদ আছে, মুছে ফেলা যাবে না');
        }

        $warehouse->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'গুদাম মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()->route('warehouses.index')->with('status', 'গুদাম মুছে ফেলা হয়েছে');
    }
}
