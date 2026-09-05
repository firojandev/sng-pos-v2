<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Finance\DataTables\IncomesDataTable;
use Modules\Finance\Http\Requests\StoreIncomeRequest;
use Modules\Finance\Http\Requests\UpdateIncomeRequest;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\Income;

class IncomeController extends Controller
{
    /**
     * Common payment methods mapping (Cash, Bank, MFS).
     *
     * @return array<string, array{bn: string, en: string}>
     */
    protected function paymentMethods(): array
    {
        return [
            'cash' => ['bn' => 'নগদ', 'en' => 'Cash'],
            'bank' => ['bn' => 'ব্যাংক', 'en' => 'Bank'],
            'mfs' => ['bn' => 'মোবাইল ব্যাংকিং (MFS)', 'en' => 'Mobile Banking (MFS)'],
        ];
    }

    /**
     * Resolve the appropriate cash account ID if payment method is cash.
     */
    protected function resolveCashAccountId(?int $accountId, ?string $paymentMethod): ?int
    {
        if ($paymentMethod === 'cash') {
            if ($accountId && $acc = Account::find($accountId)) {
                if ($acc->type === 'cash') {
                    return $acc->id;
                }
            }

            $cashAcc = Account::where('type', 'cash')->where('is_default', true)->first()
                ?? Account::where('type', 'cash')->first();

            return $cashAcc?->id ?? $accountId;
        }

        return $accountId;
    }

    public function index(IncomesDataTable $dataTable): mixed
    {
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        $metrics = [
            'totalIncome' => (float) Income::sum('amount'),
            'todayIncome' => (float) Income::whereDate('income_date', today())->sum('amount'),
            'thisMonthIncome' => (float) Income::whereYear('income_date', now()->year)
                ->whereMonth('income_date', now()->month)
                ->sum('amount'),
            'totalCount' => (int) Income::count(),
        ];

        $paymentMethods = $this->paymentMethods();

        return $dataTable->render('finance::income.index', compact('accounts', 'metrics', 'paymentMethods'));
    }

    public function create(): View
    {
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();
        $paymentMethods = $this->paymentMethods();

        return view('finance::income.create', [
            'income' => new Income,
            'accounts' => $accounts,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function store(StoreIncomeRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['account_id'] = $this->resolveCashAccountId(
            isset($data['account_id']) ? (int) $data['account_id'] : null,
            $data['payment_method'] ?? 'cash'
        );

        $income = Income::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'আয় সফলভাবে যোগ করা হয়েছে',
                'income' => $income,
            ]);
        }

        return redirect()->route('income.index')->with('status', 'আয় সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, Income $income): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $income->id,
                'source' => $income->source,
                'amount' => (float) $income->amount,
                'income_date' => optional($income->income_date)->format('Y-m-d'),
                'account_id' => $income->account_id,
                'payment_method' => $income->payment_method,
                'note' => $income->note,
                'update_url' => route('income.update', $income),
            ]);
        }

        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();
        $paymentMethods = $this->paymentMethods();

        return view('finance::income.edit', compact('income', 'accounts', 'paymentMethods'));
    }

    public function update(UpdateIncomeRequest $request, Income $income): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['account_id'] = $this->resolveCashAccountId(
            isset($data['account_id']) ? (int) $data['account_id'] : null,
            $data['payment_method'] ?? 'cash'
        );

        $income->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'আয় হালনাগাদ করা হয়েছে',
                'income' => $income,
            ]);
        }

        return redirect()->route('income.index')->with('status', 'আয় হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, Income $income): RedirectResponse|JsonResponse
    {
        $income->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'আয় মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()->route('income.index')->with('status', 'আয় মুছে ফেলা হয়েছে');
    }
}
