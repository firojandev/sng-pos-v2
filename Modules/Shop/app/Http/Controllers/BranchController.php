<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Shop\DataTables\BranchesDataTable;
use Modules\Shop\Http\Requests\StoreBranchRequest;
use Modules\Shop\Http\Requests\UpdateBranchRequest;
use Modules\Shop\Models\Branch;
use Modules\Shop\Support\PlanLimits;

class BranchController extends Controller
{
    public function index(BranchesDataTable $dataTable): mixed
    {
        return $dataTable->render('shop::branches.index');
    }

    public function create(): View
    {
        return view('shop::branches.create', ['branch' => new Branch]);
    }

    public function store(StoreBranchRequest $request): RedirectResponse|JsonResponse
    {
        $shopId = auth()->user()->shop_id;

        if ($message = PlanLimits::check($shopId, 'max_branches', Branch::count())) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('branches.index')->with('status', $message);
        }

        $branch = Branch::create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'শাখা সফলভাবে যোগ করা হয়েছে',
                'branch' => $branch,
            ]);
        }

        return redirect()->route('branches.index')->with('status', 'শাখা সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, Branch $branch): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $branch->id,
                'name' => $branch->name,
                'phone' => $branch->phone,
                'address' => $branch->address,
                'status' => $branch->status,
                'update_url' => route('branches.update', $branch),
            ]);
        }

        return view('shop::branches.edit', compact('branch'));
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse|JsonResponse
    {
        $branch->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'শাখা হালনাগাদ করা হয়েছে',
                'branch' => $branch,
            ]);
        }

        return redirect()->route('branches.index')->with('status', 'শাখা হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, Branch $branch): RedirectResponse|JsonResponse
    {
        if ($branch->warehouses()->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'এই শাখার গুদাম আছে, মুছে ফেলা যাবে না',
                ], 422);
            }

            return redirect()->route('branches.index')->with('status', 'এই শাখার গুদাম আছে, মুছে ফেলা যাবে না');
        }

        $branch->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'শাখা মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()->route('branches.index')->with('status', 'শাখা মুছে ফেলা হয়েছে');
    }
}
