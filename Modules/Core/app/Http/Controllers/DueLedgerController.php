<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Modules\Customer\Models\Customer;
use Modules\Supplier\Models\Supplier;

class DueLedgerController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type', 'customer');
        $type = in_array($type, ['customer', 'supplier'], true) ? $type : 'customer';
        $search = trim((string) $request->query('q', ''));

        $customers = $this->customerDues($search);
        $suppliers = $this->supplierDues($search);

        return view('core::due-ledger.index', [
            'type' => $type,
            'search' => $search,
            'customers' => $customers,
            'suppliers' => $suppliers,
            'customerTotalDue' => $customers->sum('total_due'),
            'supplierTotalDue' => $suppliers->sum('total_due'),
        ]);
    }

    private function customerDues(string $search): Collection
    {
        return Customer::withSum('sales', 'due_amount')
            ->with(['sales' => fn ($q) => $q->where('due_amount', '>', 0)->latest('sale_date')])
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
            ->get()
            ->map(function (Customer $customer) {
                $customer->total_due = round((float) $customer->opening_due + (float) ($customer->sales_sum_due_amount ?? 0), 2);

                return $customer;
            })
            ->filter(fn (Customer $customer) => $customer->total_due > 0)
            ->sortByDesc('total_due')
            ->values();
    }

    private function supplierDues(string $search): Collection
    {
        return Supplier::withSum('purchases', 'due_amount')
            ->with(['purchases' => fn ($q) => $q->where('due_amount', '>', 0)->latest('purchase_date')])
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
            ->get()
            ->map(function (Supplier $supplier) {
                $supplier->total_due = round((float) $supplier->opening_due + (float) ($supplier->purchases_sum_due_amount ?? 0), 2);

                return $supplier;
            })
            ->filter(fn (Supplier $supplier) => $supplier->total_due > 0)
            ->sortByDesc('total_due')
            ->values();
    }
}
