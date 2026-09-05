<?php

namespace Modules\Supplier\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Purchase\Models\Purchase;
use Modules\Supplier\DataTables\SuppliersDataTable;
use Modules\Supplier\Http\Requests\StoreSupplierRequest;
use Modules\Supplier\Http\Requests\UpdateSupplierRequest;
use Modules\Supplier\Models\Supplier;

class SupplierController extends Controller
{
    public function index(SuppliersDataTable $dataTable): mixed
    {
        $metrics = [
            'totalSuppliers' => Supplier::count(),
            'activeSuppliers' => Supplier::where('status', 'active')->count(),
            'totalDue' => round((float) Supplier::sum('opening_due') + (float) Purchase::whereNotNull('supplier_id')->sum('due_amount'), 2),
            'dueSuppliersCount' => Supplier::where(function ($q) {
                $q->where('opening_due', '>', 0)
                    ->orWhereHas('purchases', fn ($sq) => $sq->where('due_amount', '>', 0));
            })->count(),
            'totalPurchaseAmount' => round((float) Purchase::whereNotNull('supplier_id')->sum('total'), 2),
            'totalPurchaseCount' => Purchase::whereNotNull('supplier_id')->count(),
        ];

        return $dataTable->render('supplier::index', compact('metrics'));
    }

    public function create(): View
    {
        return view('supplier::create', ['supplier' => new Supplier]);
    }

    public function store(StoreSupplierRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['opening_due'] = $data['opening_due'] ?? 0;
        $supplier = Supplier::create($data);

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
        $data = $request->validated();
        if (array_key_exists('opening_due', $data)) {
            $data['opening_due'] = $data['opening_due'] ?? 0;
        }
        $supplier->update($data);

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
