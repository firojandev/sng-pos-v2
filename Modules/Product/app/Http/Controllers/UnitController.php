<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Product\Http\Requests\StoreUnitRequest;
use Modules\Product\Http\Requests\UpdateUnitRequest;
use Modules\Product\Models\Unit;

class UnitController extends Controller
{
    public function index(): View
    {
        $units = Unit::withCount('products')->latest()->paginate(10);

        return view('product::units.index', compact('units'));
    }

    public function create(): View
    {
        return view('product::units.create', ['unit' => new Unit()]);
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return redirect()->route('units.index')->with('status', 'ইউনিট সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Unit $unit): View
    {
        return view('product::units.edit', compact('unit'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()->route('units.index')->with('status', 'ইউনিট হালনাগাদ করা হয়েছে');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->products()->exists()) {
            return redirect()->route('units.index')->with('status', 'এই ইউনিট পণ্যে ব্যবহৃত হচ্ছে, মুছে ফেলা যাবে না');
        }

        $unit->delete();

        return redirect()->route('units.index')->with('status', 'ইউনিট মুছে ফেলা হয়েছে');
    }
}
