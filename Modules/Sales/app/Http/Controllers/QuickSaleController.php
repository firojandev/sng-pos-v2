<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Customer\Models\Customer;
use Modules\Sales\Http\Requests\StoreQuickSaleRequest;
use Modules\Sales\Models\Sale;

class QuickSaleController extends Controller
{
    public function create(): View
    {
        return view('sales::quick-sale.create');
    }

    public function store(StoreQuickSaleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $customer = $this->resolveCustomer($data);
        $amount = $data['amount'];

        $sale = Sale::create([
            'customer_id' => $customer?->id,
            'sale_date' => $data['sale_date'] ?? now(),
            'subtotal' => $amount,
            'discount' => 0,
            'total' => $amount,
            'paid_amount' => $amount,
            'due_amount' => 0,
            'profit' => $data['profit'] ?? null,
            'payment_status' => 'paid',
            'payment_method' => $data['payment_method'] ?? 'নগদ টাকা',
            'note' => $data['note'] ?? null,
        ]);

        $sale->update(['invoice_no' => 'SL-'.str_pad((string) $sale->id, 4, '0', STR_PAD_LEFT)]);

        return redirect()->route('sales.index')->with('status', 'দ্রুত বেচা সফলভাবে যোগ করা হয়েছে');
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $customers = Customer::query()
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")->orWhere('phone', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'phone']);

        return response()->json($customers);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveCustomer(array $data): ?Customer
    {
        if (! empty($data['customer_id'])) {
            return Customer::find($data['customer_id']);
        }

        $phone = trim((string) ($data['customer_phone'] ?? ''));
        $name = trim((string) ($data['customer_name'] ?? ''));

        if ($phone !== '') {
            return Customer::firstOrCreate(
                ['phone' => $phone],
                ['name' => $name !== '' ? $name : $phone, 'status' => 'active']
            );
        }

        if ($name !== '') {
            return Customer::firstOrCreate(
                ['name' => $name, 'phone' => null],
                ['status' => 'active']
            );
        }

        return null;
    }
}
