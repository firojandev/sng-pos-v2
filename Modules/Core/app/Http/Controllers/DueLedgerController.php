<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Core\DataTables\PurchaseDueLedgerDataTable;
use Modules\Core\DataTables\SalesDueLedgerDataTable;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\AccountTransactionService;
use Modules\Purchase\Models\Purchase;
use Modules\Sales\Models\Sale;
use Modules\Supplier\Models\Supplier;

class DueLedgerController extends Controller
{
    public function index(Request $request, SalesDueLedgerDataTable $salesDataTable, PurchaseDueLedgerDataTable $purchaseDataTable): mixed
    {
        if ($request->query('type') === 'supplier') {
            return $this->purchase($purchaseDataTable);
        }

        return $this->sales($salesDataTable);
    }

    public function sales(SalesDueLedgerDataTable $dataTable): mixed
    {
        $customerTotalDue = $this->calculateCustomerTotalDue();

        return $dataTable->render('core::due-ledger.sales', compact('customerTotalDue'));
    }

    public function purchase(PurchaseDueLedgerDataTable $dataTable): mixed
    {
        $supplierTotalDue = $this->calculateSupplierTotalDue();

        return $dataTable->render('core::due-ledger.purchase', compact('supplierTotalDue'));
    }

    public function customerDetails(Customer $customer): View
    {
        $customer->load([
            'sales' => fn ($q) => $q->where('due_amount', '>', 0)->latest('sale_date'),
        ]);
        $customer->total_due = round((float) $customer->opening_due + (float) $customer->sales->sum('due_amount'), 2);

        return view('core::due-ledger._customer_drawer', compact('customer'));
    }

    public function supplierDetails(Supplier $supplier): View
    {
        $supplier->load([
            'purchases' => fn ($q) => $q->where('due_amount', '>', 0)->latest('purchase_date'),
        ]);
        $supplier->total_due = round((float) $supplier->opening_due + (float) $supplier->purchases->sum('due_amount'), 2);

        return view('core::due-ledger._supplier_drawer', compact('supplier'));
    }

    public function customerPaymentModal(Customer $customer): View
    {
        $customer->load([
            'sales' => fn ($q) => $q->where('due_amount', '>', 0)->orderBy('sale_date', 'asc')->orderBy('id', 'asc'),
        ]);
        $customer->total_due = round((float) $customer->opening_due + (float) $customer->sales->sum('due_amount'), 2);

        $cashAccounts = Account::active()->where('type', 'cash')->get();
        $defaultCashAccount = $cashAccounts->firstWhere('is_default', true) ?? $cashAccounts->first() ?? Account::active()->first();
        $bankAccounts = Account::active()->whereIn('type', ['bank', 'mfs'])->orderByDesc('is_default')->get();
        $defaultBankAccount = $bankAccounts->firstWhere('is_default', true) ?? $bankAccounts->first();

        return view('core::due-ledger._customer_payment_modal', compact(
            'customer',
            'cashAccounts',
            'defaultCashAccount',
            'bankAccounts',
            'defaultBankAccount'
        ));
    }

    public function storeCustomerPayment(
        Request $request,
        Customer $customer,
        AccountTransactionService $accountTransactionService
    ): JsonResponse {
        $customer->load([
            'sales' => fn ($q) => $q->where('due_amount', '>', 0)->orderBy('sale_date', 'asc')->orderBy('id', 'asc'),
        ]);
        $totalOutstanding = round((float) $customer->opening_due + (float) $customer->sales->sum('due_amount'), 2);

        $validated = $request->validate([
            'payment_date' => ['nullable', 'date'],
            'payment_type' => ['nullable', 'string', 'in:cash,bank,both'],
            'note' => ['nullable', 'string', 'max:500'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'cash_account_id' => ['nullable', 'exists:accounts,id'],
            'bank_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_account_id' => ['nullable', 'exists:accounts,id'],
            'both_cash_amount' => ['nullable', 'numeric', 'min:0'],
            'both_bank_amount' => ['nullable', 'numeric', 'min:0'],
            'both_bank_account_id' => ['nullable', 'exists:accounts,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'payment_method' => ['nullable', 'string'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'opening_amount' => ['nullable', 'numeric', 'min:0'],
            'invoices' => ['nullable', 'array'],
            'invoices.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $paymentDate = ! empty($validated['payment_date']) ? $validated['payment_date'] : now()->toDateString();
        $note = $validated['note'] ?? null;
        $paymentType = $validated['payment_type'] ?? null;

        if (! $paymentType) {
            if (! empty($validated['account_id'])) {
                $acc = Account::withoutGlobalScopes()->find($validated['account_id']);
                $paymentType = ($acc && in_array($acc->type, ['bank', 'mfs'])) ? 'bank' : 'cash';
            } else {
                $paymentType = 'cash';
            }
        }

        $pools = [];

        if ($paymentType === 'both') {
            $cashAmt = round((float) ($validated['both_cash_amount'] ?? 0), 2);
            $bankAmt = round((float) ($validated['both_bank_amount'] ?? 0), 2);

            $cashAccId = $validated['cash_account_id'] ?? null;
            $cashAccount = $cashAccId
                ? Account::withoutGlobalScopes()->where('id', $cashAccId)->first()
                : Account::active()->where('type', 'cash')->orderByDesc('is_default')->first() ?? Account::active()->first();

            $bankAccId = $validated['both_bank_account_id'] ?? $validated['bank_account_id'] ?? null;
            $bankAccount = $bankAccId
                ? Account::withoutGlobalScopes()->where('id', $bankAccId)->first()
                : Account::active()->whereIn('type', ['bank', 'mfs'])->orderByDesc('is_default')->first();

            if ($cashAmt > 0 && $cashAccount) {
                $pools[] = [
                    'account' => $cashAccount,
                    'method' => 'cash',
                    'amount' => $cashAmt,
                    'remaining' => $cashAmt,
                ];
            }

            if ($bankAmt > 0 && $bankAccount) {
                $method = $bankAccount->type === 'mfs' ? 'mobile_banking' : 'bank';
                $pools[] = [
                    'account' => $bankAccount,
                    'method' => $method,
                    'amount' => $bankAmt,
                    'remaining' => $bankAmt,
                ];
            }
        } elseif ($paymentType === 'bank') {
            $bankAmt = round((float) ($validated['bank_amount'] ?? $validated['total_amount'] ?? 0), 2);
            $bankAccId = $validated['bank_account_id'] ?? $validated['account_id'] ?? null;
            $bankAccount = $bankAccId
                ? Account::withoutGlobalScopes()->where('id', $bankAccId)->first()
                : Account::active()->whereIn('type', ['bank', 'mfs'])->orderByDesc('is_default')->first();

            if ($bankAmt > 0 && $bankAccount) {
                $method = ! empty($validated['payment_method'])
                    ? $validated['payment_method']
                    : ($bankAccount->type === 'mfs' ? 'mobile_banking' : 'bank');
                $pools[] = [
                    'account' => $bankAccount,
                    'method' => $method,
                    'amount' => $bankAmt,
                    'remaining' => $bankAmt,
                ];
            }
        } else { // 'cash'
            $cashAmt = round((float) ($validated['cash_amount'] ?? $validated['total_amount'] ?? 0), 2);
            $cashAccId = $validated['cash_account_id'] ?? $validated['account_id'] ?? null;
            $cashAccount = $cashAccId
                ? Account::withoutGlobalScopes()->where('id', $cashAccId)->first()
                : Account::active()->where('type', 'cash')->orderByDesc('is_default')->first() ?? Account::active()->first();

            if ($cashAmt > 0 && $cashAccount) {
                $method = ! empty($validated['payment_method']) ? $validated['payment_method'] : 'cash';
                $pools[] = [
                    'account' => $cashAccount,
                    'method' => $method,
                    'amount' => $cashAmt,
                    'remaining' => $cashAmt,
                ];
            }
        }

        $totalPayment = round(array_sum(array_column($pools, 'amount')), 2);

        if ($totalPayment <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'পরিশোধের পরিমাণ সঠিকভাবে লিখুন (কমপক্ষে ০.০১ হতে হবে)',
            ], 422);
        }

        if ($totalPayment > $totalOutstanding) {
            return response()->json([
                'success' => false,
                'message' => 'পরিশোধের পরিমাণ মোট বকেয়ার চেয়ে বেশি হতে পারে না',
            ], 422);
        }

        DB::transaction(function () use ($customer, $validated, $totalPayment, $paymentDate, $note, $pools, $accountTransactionService) {
            $lockedCustomer = Customer::where('id', $customer->id)->lockForUpdate()->firstOrFail();

            foreach ($pools as &$pool) {
                $pool['account'] = Account::withoutGlobalScopes()->where('id', $pool['account']->id)->lockForUpdate()->firstOrFail();
            }
            unset($pool);

            $hasExplicitAllocations = isset($validated['opening_amount']) || ! empty($validated['invoices']);
            $items = [];

            if ($hasExplicitAllocations) {
                $openingPay = round((float) ($validated['opening_amount'] ?? 0), 2);
                if ($openingPay > 0 && (float) $lockedCustomer->opening_due > 0) {
                    $deductOpening = min($openingPay, (float) $lockedCustomer->opening_due);
                    $items[] = [
                        'type' => 'opening',
                        'needed' => $deductOpening,
                    ];
                }

                $invoices = $validated['invoices'] ?? [];
                foreach ($invoices as $saleId => $amount) {
                    $payAmount = round((float) $amount, 2);
                    if ($payAmount <= 0) {
                        continue;
                    }

                    $sale = Sale::where('id', $saleId)
                        ->where('customer_id', $lockedCustomer->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $sale || (float) $sale->due_amount <= 0) {
                        continue;
                    }

                    $actualPay = min($payAmount, (float) $sale->due_amount);
                    $items[] = [
                        'type' => 'sale',
                        'model' => $sale,
                        'needed' => $actualPay,
                    ];
                }
            }

            if (empty($items)) {
                $remainingToAllocate = $totalPayment;

                if ((float) $lockedCustomer->opening_due > 0 && $remainingToAllocate > 0) {
                    $deductOpening = min($remainingToAllocate, (float) $lockedCustomer->opening_due);
                    $items[] = [
                        'type' => 'opening',
                        'needed' => $deductOpening,
                    ];
                    $remainingToAllocate = round($remainingToAllocate - $deductOpening, 2);
                }

                if ($remainingToAllocate > 0) {
                    $sales = Sale::where('customer_id', $lockedCustomer->id)
                        ->where('due_amount', '>', 0)
                        ->orderBy('sale_date', 'asc')
                        ->orderBy('id', 'asc')
                        ->lockForUpdate()
                        ->get();

                    foreach ($sales as $sale) {
                        if ($remainingToAllocate <= 0) {
                            break;
                        }

                        $pay = min($remainingToAllocate, (float) $sale->due_amount);
                        $items[] = [
                            'type' => 'sale',
                            'model' => $sale,
                            'needed' => $pay,
                        ];
                        $remainingToAllocate = round($remainingToAllocate - $pay, 2);
                    }
                }
            }

            foreach ($items as $item) {
                $needed = $item['needed'];

                foreach ($pools as &$pool) {
                    if ($needed <= 0) {
                        break;
                    }
                    if ($pool['remaining'] <= 0) {
                        continue;
                    }

                    $payPortion = min($needed, $pool['remaining']);
                    $pool['remaining'] = round($pool['remaining'] - $payPortion, 2);
                    $needed = round($needed - $payPortion, 2);

                    if ($item['type'] === 'opening') {
                        $lockedCustomer->opening_due = round(max((float) $lockedCustomer->opening_due - $payPortion, 0), 2);
                        $lockedCustomer->save();

                        $accountTransactionService->recordTransaction(
                            account: $pool['account'],
                            type: 'in',
                            amount: $payPortion,
                            source: 'sale',
                            sourceable: $lockedCustomer,
                            note: 'গ্রাহক প্রারম্ভিক বাকি আদায়: '.$lockedCustomer->name.($note ? " ({$note})" : ''),
                            occurredAt: $paymentDate.' '.now()->format('H:i:s'),
                            userId: Auth::id()
                        );
                    } elseif ($item['type'] === 'sale') {
                        /** @var Sale $sale */
                        $sale = $item['model'];
                        $sale->payments()->create([
                            'account_id' => $pool['account']->id,
                            'method' => $pool['method'],
                            'amount' => $payPortion,
                            'payment_date' => $paymentDate,
                            'note' => $note ?? ('বাকি আদায় - ইনভয়েস: '.$sale->invoice_no),
                        ]);

                        $newPaid = round((float) $sale->paid_amount + $payPortion, 2);
                        $newDue = round(max((float) $sale->due_amount - $payPortion, 0), 2);
                        $sale->update([
                            'paid_amount' => $newPaid,
                            'due_amount' => $newDue,
                            'payment_status' => $newDue <= 0 ? 'paid' : 'partial',
                        ]);
                    }
                }
                unset($pool);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'বাকি আদায় সফলভাবে সম্পন্ন হয়েছে',
        ]);
    }

    public function supplierPaymentModal(Supplier $supplier): View
    {
        $supplier->load([
            'purchases' => fn ($q) => $q->where('due_amount', '>', 0)->orderBy('purchase_date', 'asc')->orderBy('id', 'asc'),
        ]);
        $supplier->total_due = round((float) $supplier->opening_due + (float) $supplier->purchases->sum('due_amount'), 2);

        $cashAccounts = Account::active()->where('type', 'cash')->get();
        $defaultCashAccount = $cashAccounts->firstWhere('is_default', true) ?? $cashAccounts->first() ?? Account::active()->first();
        $bankAccounts = Account::active()->whereIn('type', ['bank', 'mfs'])->orderByDesc('is_default')->get();
        $defaultBankAccount = $bankAccounts->firstWhere('is_default', true) ?? $bankAccounts->first();

        return view('core::due-ledger._supplier_payment_modal', compact(
            'supplier',
            'cashAccounts',
            'defaultCashAccount',
            'bankAccounts',
            'defaultBankAccount'
        ));
    }

    public function storeSupplierPayment(
        Request $request,
        Supplier $supplier,
        AccountTransactionService $accountTransactionService
    ): JsonResponse {
        $supplier->load([
            'purchases' => fn ($q) => $q->where('due_amount', '>', 0)->orderBy('purchase_date', 'asc')->orderBy('id', 'asc'),
        ]);
        $totalOutstanding = round((float) $supplier->opening_due + (float) $supplier->purchases->sum('due_amount'), 2);

        $validated = $request->validate([
            'payment_date' => ['nullable', 'date'],
            'payment_type' => ['nullable', 'string', 'in:cash,bank,both'],
            'note' => ['nullable', 'string', 'max:500'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'cash_account_id' => ['nullable', 'exists:accounts,id'],
            'bank_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_account_id' => ['nullable', 'exists:accounts,id'],
            'both_cash_amount' => ['nullable', 'numeric', 'min:0'],
            'both_bank_amount' => ['nullable', 'numeric', 'min:0'],
            'both_bank_account_id' => ['nullable', 'exists:accounts,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'payment_method' => ['nullable', 'string'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'opening_amount' => ['nullable', 'numeric', 'min:0'],
            'invoices' => ['nullable', 'array'],
            'invoices.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $paymentDate = ! empty($validated['payment_date']) ? $validated['payment_date'] : now()->toDateString();
        $note = $validated['note'] ?? null;
        $paymentType = $validated['payment_type'] ?? null;

        if (! $paymentType) {
            if (! empty($validated['account_id'])) {
                $acc = Account::withoutGlobalScopes()->find($validated['account_id']);
                $paymentType = ($acc && in_array($acc->type, ['bank', 'mfs'])) ? 'bank' : 'cash';
            } else {
                $paymentType = 'cash';
            }
        }

        $pools = [];

        if ($paymentType === 'both') {
            $cashAmt = round((float) ($validated['both_cash_amount'] ?? 0), 2);
            $bankAmt = round((float) ($validated['both_bank_amount'] ?? 0), 2);

            $cashAccId = $validated['cash_account_id'] ?? null;
            $cashAccount = $cashAccId
                ? Account::withoutGlobalScopes()->where('id', $cashAccId)->first()
                : Account::active()->where('type', 'cash')->orderByDesc('is_default')->first() ?? Account::active()->first();

            $bankAccId = $validated['both_bank_account_id'] ?? $validated['bank_account_id'] ?? null;
            $bankAccount = $bankAccId
                ? Account::withoutGlobalScopes()->where('id', $bankAccId)->first()
                : Account::active()->whereIn('type', ['bank', 'mfs'])->orderByDesc('is_default')->first();

            if ($cashAmt > 0 && $cashAccount) {
                $pools[] = [
                    'account' => $cashAccount,
                    'method' => 'cash',
                    'amount' => $cashAmt,
                    'remaining' => $cashAmt,
                ];
            }

            if ($bankAmt > 0 && $bankAccount) {
                $method = $bankAccount->type === 'mfs' ? 'mobile_banking' : 'bank';
                $pools[] = [
                    'account' => $bankAccount,
                    'method' => $method,
                    'amount' => $bankAmt,
                    'remaining' => $bankAmt,
                ];
            }
        } elseif ($paymentType === 'bank') {
            $bankAmt = round((float) ($validated['bank_amount'] ?? $validated['total_amount'] ?? 0), 2);
            $bankAccId = $validated['bank_account_id'] ?? $validated['account_id'] ?? null;
            $bankAccount = $bankAccId
                ? Account::withoutGlobalScopes()->where('id', $bankAccId)->first()
                : Account::active()->whereIn('type', ['bank', 'mfs'])->orderByDesc('is_default')->first();

            if ($bankAmt > 0 && $bankAccount) {
                $method = ! empty($validated['payment_method'])
                    ? $validated['payment_method']
                    : ($bankAccount->type === 'mfs' ? 'mobile_banking' : 'bank');
                $pools[] = [
                    'account' => $bankAccount,
                    'method' => $method,
                    'amount' => $bankAmt,
                    'remaining' => $bankAmt,
                ];
            }
        } else { // 'cash'
            $cashAmt = round((float) ($validated['cash_amount'] ?? $validated['total_amount'] ?? 0), 2);
            $cashAccId = $validated['cash_account_id'] ?? $validated['account_id'] ?? null;
            $cashAccount = $cashAccId
                ? Account::withoutGlobalScopes()->where('id', $cashAccId)->first()
                : Account::active()->where('type', 'cash')->orderByDesc('is_default')->first() ?? Account::active()->first();

            if ($cashAmt > 0 && $cashAccount) {
                $method = ! empty($validated['payment_method']) ? $validated['payment_method'] : 'cash';
                $pools[] = [
                    'account' => $cashAccount,
                    'method' => $method,
                    'amount' => $cashAmt,
                    'remaining' => $cashAmt,
                ];
            }
        }

        $totalPayment = round(array_sum(array_column($pools, 'amount')), 2);

        if ($totalPayment <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'পরিশোধের পরিমাণ সঠিকভাবে লিখুন (কমপক্ষে ০.০১ হতে হবে)',
            ], 422);
        }

        if ($totalPayment > $totalOutstanding) {
            return response()->json([
                'success' => false,
                'message' => 'পরিশোধের পরিমাণ মোট বকেয়ার চেয়ে বেশি হতে পারে না',
            ], 422);
        }

        DB::transaction(function () use ($supplier, $validated, $totalPayment, $paymentDate, $note, $pools, $accountTransactionService) {
            $lockedSupplier = Supplier::where('id', $supplier->id)->lockForUpdate()->firstOrFail();

            foreach ($pools as &$pool) {
                $pool['account'] = Account::withoutGlobalScopes()->where('id', $pool['account']->id)->lockForUpdate()->firstOrFail();
            }
            unset($pool);

            $hasExplicitAllocations = isset($validated['opening_amount']) || ! empty($validated['invoices']);
            $items = [];

            if ($hasExplicitAllocations) {
                $openingPay = round((float) ($validated['opening_amount'] ?? 0), 2);
                if ($openingPay > 0 && (float) $lockedSupplier->opening_due > 0) {
                    $deductOpening = min($openingPay, (float) $lockedSupplier->opening_due);
                    $items[] = [
                        'type' => 'opening',
                        'needed' => $deductOpening,
                    ];
                }

                $invoices = $validated['invoices'] ?? [];
                foreach ($invoices as $purchaseId => $amount) {
                    $payAmount = round((float) $amount, 2);
                    if ($payAmount <= 0) {
                        continue;
                    }

                    $purchase = Purchase::where('id', $purchaseId)
                        ->where('supplier_id', $lockedSupplier->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $purchase || (float) $purchase->due_amount <= 0) {
                        continue;
                    }

                    $actualPay = min($payAmount, (float) $purchase->due_amount);
                    $items[] = [
                        'type' => 'purchase',
                        'model' => $purchase,
                        'needed' => $actualPay,
                    ];
                }
            }

            if (empty($items)) {
                $remainingToAllocate = $totalPayment;

                if ((float) $lockedSupplier->opening_due > 0 && $remainingToAllocate > 0) {
                    $deductOpening = min($remainingToAllocate, (float) $lockedSupplier->opening_due);
                    $items[] = [
                        'type' => 'opening',
                        'needed' => $deductOpening,
                    ];
                    $remainingToAllocate = round($remainingToAllocate - $deductOpening, 2);
                }

                if ($remainingToAllocate > 0) {
                    $purchases = Purchase::where('supplier_id', $lockedSupplier->id)
                        ->where('due_amount', '>', 0)
                        ->orderBy('purchase_date', 'asc')
                        ->orderBy('id', 'asc')
                        ->lockForUpdate()
                        ->get();

                    foreach ($purchases as $purchase) {
                        if ($remainingToAllocate <= 0) {
                            break;
                        }

                        $pay = min($remainingToAllocate, (float) $purchase->due_amount);
                        $items[] = [
                            'type' => 'purchase',
                            'model' => $purchase,
                            'needed' => $pay,
                        ];
                        $remainingToAllocate = round($remainingToAllocate - $pay, 2);
                    }
                }
            }

            foreach ($items as $item) {
                $needed = $item['needed'];

                foreach ($pools as &$pool) {
                    if ($needed <= 0) {
                        break;
                    }
                    if ($pool['remaining'] <= 0) {
                        continue;
                    }

                    $payPortion = min($needed, $pool['remaining']);
                    $pool['remaining'] = round($pool['remaining'] - $payPortion, 2);
                    $needed = round($needed - $payPortion, 2);

                    if ($item['type'] === 'opening') {
                        $lockedSupplier->opening_due = round(max((float) $lockedSupplier->opening_due - $payPortion, 0), 2);
                        $lockedSupplier->save();

                        $accountTransactionService->recordTransaction(
                            account: $pool['account'],
                            type: 'out',
                            amount: $payPortion,
                            source: 'purchase',
                            sourceable: $lockedSupplier,
                            note: 'সরবরাহকারী প্রারম্ভিক বাকি পরিশোধ: '.$lockedSupplier->name.($note ? " ({$note})" : ''),
                            occurredAt: $paymentDate.' '.now()->format('H:i:s'),
                            userId: Auth::id()
                        );
                    } elseif ($item['type'] === 'purchase') {
                        /** @var Purchase $purchase */
                        $purchase = $item['model'];
                        $purchase->payments()->create([
                            'account_id' => $pool['account']->id,
                            'method' => $pool['method'],
                            'amount' => $payPortion,
                            'payment_date' => $paymentDate,
                            'note' => $note ?? ('বাকি পরিশোধ - বিল: '.$purchase->invoice_no),
                        ]);

                        $newPaid = round((float) $purchase->paid_amount + $payPortion, 2);
                        $newDue = round(max((float) $purchase->due_amount - $payPortion, 0), 2);
                        $purchase->update([
                            'paid_amount' => $newPaid,
                            'due_amount' => $newDue,
                            'payment_status' => $newDue <= 0 ? 'paid' : 'partial',
                        ]);
                    }
                }
                unset($pool);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'বাকি পরিশোধ সফলভাবে সম্পন্ন হয়েছে',
        ]);
    }

    protected function calculateCustomerTotalDue(): float
    {
        $opening = (float) Customer::query()->sum('opening_due');
        $salesDue = (float) Sale::query()->sum('due_amount');

        return round($opening + $salesDue, 2);
    }

    protected function calculateSupplierTotalDue(): float
    {
        $opening = (float) Supplier::query()->sum('opening_due');
        $purchaseDue = (float) Purchase::query()->sum('due_amount');

        return round($opening + $purchaseDue, 2);
    }
}
