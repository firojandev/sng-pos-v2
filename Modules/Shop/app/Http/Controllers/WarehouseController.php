<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $data = $request->validated();
        $isDefault = (bool) ($data['is_default'] ?? false);

        if (Warehouse::count() === 0) {
            $isDefault = true;
        }

        if ($isDefault && ($data['status'] ?? '') === 'inactive') {
            $errorMsg = 'নিষ্ক্রিয় গুদামকে ডিফল্ট হিসেবে নির্ধারণ করা যাবে না।';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'errors' => ['status' => [$errorMsg]],
                ], 422);
            }

            return redirect()->back()->withInput()->withErrors(['status' => $errorMsg]);
        }

        $warehouse = DB::transaction(function () use ($data, $isDefault) {
            $data['is_default'] = $isDefault;
            $warehouse = Warehouse::create($data);

            if ($isDefault) {
                $warehouse->makeDefault();
            }

            return $warehouse;
        });

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
                'is_default' => (bool) $warehouse->is_default,
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
        $data = $request->validated();
        $isDefault = array_key_exists('is_default', $data) ? (bool) $data['is_default'] : (bool) $warehouse->is_default;
        $newStatus = $data['status'] ?? $warehouse->status;

        if ($isDefault && $newStatus === 'inactive') {
            $errorMsg = 'নিষ্ক্রিয় গুদামকে ডিফল্ট হিসেবে নির্ধারণ করা যাবে না।';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'errors' => ['status' => [$errorMsg]],
                ], 422);
            }

            return redirect()->back()->withInput()->withErrors(['status' => $errorMsg]);
        }

        if ($warehouse->is_default && $newStatus === 'inactive') {
            $errorMsg = 'ডিফল্ট গুদামকে নিষ্ক্রিয় করা যাবে না। অন্য কোনো গুদামকে ডিফল্ট হিসেবে নির্ধারণ করে এটিকে নিষ্ক্রিয় করুন।';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'errors' => ['status' => [$errorMsg]],
                ], 422);
            }

            return redirect()->back()->withInput()->withErrors(['status' => $errorMsg]);
        }

        DB::transaction(function () use ($warehouse, $data, $isDefault) {
            $data['is_default'] = $isDefault;
            $warehouse->update($data);

            if ($isDefault) {
                $warehouse->makeDefault();
            }
        });

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
        if ($warehouse->is_default && Warehouse::where('id', '!=', $warehouse->id)->exists()) {
            $errorMsg = 'ডিফল্ট গুদাম মুছে ফেলা যাবে না। অন্য কোনো গুদামকে ডিফল্ট হিসেবে নির্ধারণ করে এটি মুছুন।';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                ], 422);
            }

            return redirect()->route('warehouses.index')->withErrors(['error' => $errorMsg]);
        }

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

    public function setDefault(Request $request, Warehouse $warehouse): RedirectResponse|JsonResponse
    {
        if ($warehouse->status !== 'active') {
            $errorMsg = 'নিষ্ক্রিয় গুদামকে ডিফল্ট হিসেবে নির্ধারণ করা যাবে না। প্রথমে এটিকে সক্রিয় করুন।';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                ], 422);
            }

            return redirect()->route('warehouses.index')->withErrors(['error' => $errorMsg]);
        }

        $warehouse->makeDefault();

        $successMsg = "'{$warehouse->name}' গুদামটিকে ডিফল্ট গুদাম হিসেবে নির্ধারণ করা হয়েছে";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'warehouse' => $warehouse,
            ]);
        }

        return redirect()->route('warehouses.index')->with('status', $successMsg);
    }
}
