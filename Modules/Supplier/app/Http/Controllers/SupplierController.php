<?php

namespace Modules\Supplier\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Supplier\Http\Requests\StoreSupplierRequest;
use Modules\Supplier\Http\Requests\UpdateSupplierRequest;
use Modules\Supplier\Models\Supplier;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::withSum('purchases', 'due_amount')->latest()->paginate(10);

        return view('supplier::index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('supplier::create', ['supplier' => new Supplier()]);
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());

        return redirect()->route('suppliers.index')->with('status', 'সরবরাহকারী সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Supplier $supplier): View
    {
        return view('supplier::edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')->with('status', 'সরবরাহকারী হালনাগাদ করা হয়েছে');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchases()->exists()) {
            return redirect()->route('suppliers.index')->with('status', 'এই সরবরাহকারীর ক্রয় রেকর্ড আছে, মুছে ফেলা যাবে না');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('status', 'সরবরাহকারী মুছে ফেলা হয়েছে');
    }
}
