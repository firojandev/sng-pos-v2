<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Employee\Models\Employee;
use Modules\Payroll\Http\Controllers\Concerns\WorksOnPayroll;
use Modules\Payroll\Models\FinalSettlement;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Services\FinalSettlementService;
use Modules\Payroll\Services\PayrollService;
use Modules\Shop\Models\Shop;

/**
 * Final settlements of staff leaving the current shop.
 */
class FinalSettlementController extends Controller
{
    use WorksOnPayroll;

    public function __construct(private FinalSettlementService $settlements) {}

    public function index(): View
    {
        $this->settings();
        $settled = FinalSettlement::pluck('employee_id');

        return view('payroll::settlements.index', [
            'settlements' => FinalSettlement::whereIn('employee_id', Employee::workingAtShop()->pluck('id'))
                ->with('employee:id,name,employee_code')
                ->latest('separation_date')
                ->paginate(25),
            'employees' => Employee::workingAtShop()->whereNotIn('id', $settled)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', Rule::in(Employee::pluck('id')->all())],
            'separation_type' => ['required', Rule::in(FinalSettlement::SEPARATION_TYPES)],
            'separation_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $settlement = $this->settlements->prepare(
            Employee::findOrFail($validated['employee_id']),
            $validated['separation_type'],
            Carbon::parse($validated['separation_date']),
            $validated['note'] ?? null,
        );

        return redirect()->route('payroll.settlements.show', $settlement)->with('status', 'নিষ্পত্তির খসড়া তৈরি হয়েছে; অঙ্কগুলো যাচাই করুন');
    }

    public function show(FinalSettlement $settlement): View
    {
        $settlement->load(['items', 'employee', 'payments.account:id,name', 'finalizer:id,name']);

        return view('payroll::settlements.show', [
            'settlement' => $settlement,
            'accounts' => $this->moneyAccounts(),
            'shop' => Shop::find($settlement->shop_id),
            'unpaidPayslips' => Payslip::where('employee_id', $settlement->employee_id)
                ->whereColumn('paid_amount', '<', 'net_pay')
                ->with('run')
                ->get(),
        ]);
    }

    public function update(Request $request, FinalSettlement $settlement): RedirectResponse
    {
        $validated = $request->validate([
            'amounts' => ['array'],
            'amounts.*' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $this->settlements->update($settlement, $validated['amounts'] ?? [], $validated['note'] ?? null);

        return back()->with('status', 'নিষ্পত্তি হালনাগাদ হয়েছে');
    }

    public function finalize(FinalSettlement $settlement): RedirectResponse
    {
        $this->settlements->finalize($settlement);

        return back()->with('status', 'নিষ্পত্তি চূড়ান্ত হয়েছে এবং হিসাবে পোস্ট হয়েছে');
    }

    public function reopen(FinalSettlement $settlement): RedirectResponse
    {
        $this->settlements->reopen($settlement);

        return back()->with('status', 'নিষ্পত্তি আবার খসড়া করা হয়েছে');
    }

    public function destroy(FinalSettlement $settlement): RedirectResponse
    {
        $this->settlements->delete($settlement);

        return redirect()->route('payroll.settlements.index')->with('status', 'নিষ্পত্তির খসড়া মুছে ফেলা হয়েছে');
    }

    public function pay(Request $request, FinalSettlement $settlement, PayrollService $payroll): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'paid_on' => ['required', 'date'],
        ]);

        $payroll->pay($settlement, $this->companyAccount((int) $validated['account_id']), (float) $validated['amount'], Carbon::parse($validated['paid_on']));

        return back()->with('status', 'নিষ্পত্তির অর্থ পরিশোধ হয়েছে');
    }
}
