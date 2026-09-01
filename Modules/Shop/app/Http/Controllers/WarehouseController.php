<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Shop\Http\Requests\StoreWarehouseRequest;
use Modules\Shop\Http\Requests\UpdateWarehouseRequest;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Warehouse;
use Modules\Shop\Support\PlanLimits;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $warehouses = Warehouse::with('branch')->withCount('batches')->orderBy('name')->paginate(10);

        return view('shop::warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        $branches = Branch::where('status', 'active')->orderBy('name')->get();

        return view('shop::warehouses.create', ['warehouse' => new Warehouse, 'branches' => $branches]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id;

        if ($message = PlanLimits::check($shopId, 'max_warehouses', Warehouse::count())) {
            return redirect()->route('warehouses.index')->with('status', $message);
        }

        Warehouse::create($request->validated());

        return redirect()->route('warehouses.index')->with('status', 'গুদাম সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Warehouse $warehouse): View
    {
        $branches = Branch::where('status', 'active')->orderBy('name')->get();

        return view('shop::warehouses.edit', compact('warehouse', 'branches'));
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update($request->validated());

        return redirect()->route('warehouses.index')->with('status', 'গুদাম হালনাগাদ করা হয়েছে');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->batches()->exists()) {
            return redirect()->route('warehouses.index')->with('status', 'এই গুদামে পণ্যের মজুদ আছে, মুছে ফেলা যাবে না');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('status', 'গুদাম মুছে ফেলা হয়েছে');
    }
}
