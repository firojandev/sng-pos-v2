<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Customer\DataTables\CustomersDataTable;
use Modules\Customer\Http\Requests\StoreCustomerRequest;
use Modules\Customer\Http\Requests\UpdateCustomerRequest;
use Modules\Customer\Models\Customer;
use Modules\Sales\Models\Sale;

class CustomerController extends Controller
{
    public function index(CustomersDataTable $dataTable): mixed
    {
        $metrics = [
            'totalCustomers' => Customer::count(),
            'activeCustomers' => Customer::where('status', 'active')->count(),
            'totalDue' => round((float) Customer::sum('opening_due') + (float) Sale::whereNotNull('customer_id')->sum('due_amount'), 2),
            'dueCustomersCount' => Customer::where(function ($q) {
                $q->where('opening_due', '>', 0)
                    ->orWhereHas('sales', fn ($sq) => $sq->where('due_amount', '>', 0));
            })->count(),
            'totalSalesAmount' => round((float) Sale::whereNotNull('customer_id')->sum('total'), 2),
            'totalSalesCount' => Sale::whereNotNull('customer_id')->count(),
        ];

        return $dataTable->render('customer::index', compact('metrics'));
    }

    public function create(): View
    {
        return view('customer::create', ['customer' => new Customer]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['opening_due'] = $data['opening_due'] ?? 0;
        $customer = Customer::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'গ্রাহক সফলভাবে যোগ করা হয়েছে',
                'customer' => $customer,
            ]);
        }

        return redirect()->route('customers.index')->with('status', 'গ্রাহক সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, Customer $customer): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'address' => $customer->address,
                'opening_due' => (float) $customer->opening_due,
                'status' => $customer->status,
                'update_url' => route('customers.update', $customer),
            ]);
        }

        return view('customer::edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        if (array_key_exists('opening_due', $data)) {
            $data['opening_due'] = $data['opening_due'] ?? 0;
        }
        $customer->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'গ্রাহক হালনাগাদ করা হয়েছে',
                'customer' => $customer,
            ]);
        }

        return redirect()->route('customers.index')->with('status', 'গ্রাহক হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse|JsonResponse
    {
        if ($customer->sales()->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'এই গ্রাহকের বিক্রয় রেকর্ড আছে, মুছে ফেলা যাবে না',
                ], 422);
            }

            return redirect()->route('customers.index')->with('status', 'এই গ্রাহকের বিক্রয় রেকর্ড আছে, মুছে ফেলা যাবে না');
        }

        $customer->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'গ্রাহক মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()->route('customers.index')->with('status', 'গ্রাহক মুছে ফেলা হয়েছে');
    }
}
