<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Account;
use Modules\Sales\Http\Requests\StoreQuickSaleRequest;
use Modules\Sales\Models\Sale;

class QuickSaleController extends Controller
{
    public function create(): View
    {
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();
        $bankAccounts = $accounts->whereIn('type', ['bank', 'mfs']);
        $defaultCashAccount = $accounts->firstWhere('type', 'cash');
        $defaultBankAccount = $bankAccounts->firstWhere('is_default', true) ?? $bankAccounts->first();

        return view('sales::quick-sale.create', compact('accounts', 'bankAccounts', 'defaultCashAccount', 'defaultBankAccount'));
    }

    public function store(StoreQuickSaleRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $customer = $this->resolveCustomer($data);

        $sale = DB::transaction(function () use ($data, $customer) {
            $paymentType = $data['payment_type'] ?? 'cash';
            $defaultCashAccount = Account::where('type', 'cash')->orderByDesc('is_default')->first();
            $bankAccount = ! empty($data['account_id']) ? Account::find($data['account_id']) : null;

            $paymentsToCreate = [];
            $totalAmount = 0.0;
            $paymentMethodLabel = 'নগদ টাকা';

            if ($paymentType === 'cash') {
                $totalAmount = round((float) $data['amount'], 2);
                $paymentMethodLabel = 'নগদ টাকা';
                $paymentsToCreate[] = [
                    'account_id' => ! empty($data['account_id']) ? (int) $data['account_id'] : $defaultCashAccount?->id,
                    'method' => 'cash',
                    'amount' => $totalAmount,
                ];
            } elseif ($paymentType === 'bank') {
                $totalAmount = round((float) $data['amount'], 2);
                $method = $bankAccount?->type === 'mfs' ? 'mobile_banking' : 'bank';
                $paymentMethodLabel = $bankAccount?->type === 'mfs' ? 'মোবাইল ব্যাংকিং' : 'ব্যাংক';
                $paymentsToCreate[] = [
                    'account_id' => $bankAccount?->id ?? (! empty($data['account_id']) ? (int) $data['account_id'] : null),
                    'method' => $method,
                    'amount' => $totalAmount,
                ];
            } elseif ($paymentType === 'both') {
                $cashAmount = round((float) ($data['cash_amount'] ?? 0), 2);
                $bankAmount = round((float) ($data['bank_amount'] ?? 0), 2);
                $totalAmount = round($cashAmount + $bankAmount, 2);
                $paymentMethodLabel = 'উভয় (ক্যাশ + ব্যাংক)';

                if ($cashAmount > 0) {
                    $paymentsToCreate[] = [
                        'account_id' => $defaultCashAccount?->id,
                        'method' => 'cash',
                        'amount' => $cashAmount,
                    ];
                }
                if ($bankAmount > 0) {
                    $method = $bankAccount?->type === 'mfs' ? 'mobile_banking' : 'bank';
                    $paymentsToCreate[] = [
                        'account_id' => $bankAccount?->id ?? (! empty($data['account_id']) ? (int) $data['account_id'] : null),
                        'method' => $method,
                        'amount' => $bankAmount,
                    ];
                }
            }

            $sale = Sale::create([
                'customer_id' => $customer?->id,
                'sale_date' => $data['sale_date'] ?? now(),
                'subtotal' => $totalAmount,
                'discount' => 0,
                'total' => $totalAmount,
                'paid_amount' => $totalAmount,
                'due_amount' => 0,
                'profit' => $data['profit'] ?? null,
                'payment_status' => 'paid',
                'payment_method' => $paymentMethodLabel,
                'note' => $data['note'] ?? null,
            ]);

            $sale->update(['invoice_no' => 'SL-'.str_pad((string) $sale->id, 4, '0', STR_PAD_LEFT)]);

            foreach ($paymentsToCreate as $p) {
                $sale->payments()->create([
                    'account_id' => $p['account_id'],
                    'method' => $p['method'],
                    'amount' => $p['amount'],
                    'payment_date' => $sale->sale_date ?? now()->toDateString(),
                ]);
            }

            return $sale;
        });

        if ($request->wantsJson() || $request->ajax()) {
            $canPrint = auth()->user()?->can('sales.print') ?? true;

            return response()->json([
                'success' => true,
                'message' => 'দ্রুত বেচা সফলভাবে সম্পন্ন হয়েছে',
                'message_en' => 'Quick sale completed successfully',
                'sale' => [
                    'id' => $sale->id,
                    'invoice_no' => $sale->invoice_no,
                    'total' => (float) $sale->total,
                    'paid_amount' => (float) $sale->paid_amount,
                    'payment_method' => $sale->payment_method,
                    'customer_name' => $customer?->name ?? 'ওয়াক-ইন গ্রাহক (Walk-in)',
                    'print_url' => $canPrint ? route('sales.print-invoice', $sale) : null,
                    'invoice_modal_url' => route('sales.invoice-modal', $sale),
                ],
            ]);
        }

        return redirect()->route('sales.index')
            ->with('status', 'দ্রুত বেচা সফলভাবে যোগ করা হয়েছে')
            ->with('show_invoice_sale_id', $sale->id);
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
