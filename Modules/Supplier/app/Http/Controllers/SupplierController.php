<?php

namespace Modules\Supplier\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Supplier\DataTables\SuppliersDataTable;
use Modules\Supplier\Http\Requests\StoreSupplierRequest;
use Modules\Supplier\Http\Requests\UpdateSupplierRequest;
use Modules\Supplier\Models\Supplier;

class SupplierController extends Controller
{
    public function index(SuppliersDataTable $dataTable): mixed
    {
        return $dataTable->render('supplier::index');
    }

    public function create(): View
    {
        return view('supplier::create', ['supplier' => new Supplier]);
    }

    public function store(StoreSupplierRequest $request): RedirectResponse|JsonResponse
    {
        $supplier = Supplier::create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'সরবরাহকারী সফলভাবে যোগ করা হয়েছে',
                'supplier' => $supplier,
            ]);
        }

        return redirect()->route('suppliers.index')->with('status', 'সরবরাহকারী সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, Supplier $supplier): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $supplier->id,
                'name' => $supplier->name,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'address' => $supplier->address,
                'opening_due' => (float) $supplier->opening_due,
                'status' => $supplier->status,
                'update_url' => route('suppliers.update', $supplier),
            ]);
        }

        return view('supplier::edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse|JsonResponse
    {
        $supplier->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'সরবরাহকারী হালনাগাদ করা হয়েছে',
                'supplier' => $supplier,
            ]);
        }

        return redirect()->route('suppliers.index')->with('status', 'সরবরাহকারী হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, Supplier $supplier): RedirectResponse|JsonResponse
    {
        if ($supplier->purchases()->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'এই সরবরাহকারীর ক্রয় রেকর্ড আছে, মুছে ফেলা যাবে না',
                ], 422);
            }

            return redirect()->route('suppliers.index')->with('status', 'এই সরবরাহকারীর ক্রয় রেকর্ড আছে, মুছে ফেলা যাবে না');
        }

        $supplier->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'সরবরাহকারী মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()->route('suppliers.index')->with('status', 'সরবরাহকারী মুছে ফেলা হয়েছে');
    }
}
