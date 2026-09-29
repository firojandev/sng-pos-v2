<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Payroll\Http\Controllers\Concerns\WorksOnPayroll;
use Modules\Payroll\Models\PayrollPayment;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Services\PayrollService;
use Modules\Shop\Models\Shop;

/**
 * Monthly salary and festival bonus runs of the current shop.
 */
class PayrollRunController extends Controller
{
    use WorksOnPayroll;

    public function __construct(private PayrollService $payroll) {}

    public function index(Request $request): View
    {
        $this->settings();

        // The current shop's runs; in the company workspace, every shop's.
        $runs = PayrollRun::query()
            ->when($this->shopId(), fn ($query) => $query->where('shop_id', $this->shopId()))
            ->with('shop:id,name')
            ->when($request->filled('year'), fn ($query) => $query->whereYear('month', $request->integer('year')))
            ->latest('month')
            ->latest('id')
            ->paginate(24)
            ->withQueryString();

        return view('payroll::runs.index', [
            'runs' => $runs,
            'shops' => $this->shopId() ? collect() : Shop::where('company_id', $this->companyId())->orderBy('name')->pluck('name', 'id'),
            'nextMonth' => now()->subMonthNoOverflow()->format('Y-m'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'type' => ['required', Rule::in(['salary', 'bonus'])],
            'title' => ['nullable', 'required_if:type,bonus', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
            'shop_id' => [$this->shopId() ? 'nullable' : 'required', 'integer', Rule::in(Shop::where('company_id', $this->companyId())->pluck('id')->all())],
        ]);

        $run = $this->payroll->create(
            $this->shopId() ?: (int) $validated['shop_id'],
            Carbon::createFromFormat('Y-m-d', $validated['month'].'-01'),
            $validated['type'],
            $validated['title'] ?? null,
            $validated['note'] ?? null,
        );

        return redirect()->route('payroll.runs.show', $run)->with('status', "{$run->employees_count} জনের পে-স্লিপ তৈরি হয়েছে");
    }

    public function show(PayrollRun $run): View
    {
        $run->load(['payslips' => fn ($query) => $query->orderBy('employee_name')->with('items'), 'shop:id,name', 'approver:id,name']);

        return view('payroll::runs.show', [
            'run' => $run,
            'accounts' => $this->moneyAccounts(),
            'payments' => PayrollPayment::where('payable_type', (new Payslip)->getMorphClass())
                ->whereIn('payable_id', $run->payslips->pluck('id'))
                ->with(['account:id,name', 'employee:id,name'])
                ->latest('id')
                ->get(),
        ]);
    }

    public function recalculate(PayrollRun $run): RedirectResponse
    {
        $this->payroll->calculate($run);

        return back()->with('status', 'পে-রোল আবার হিসাব করা হয়েছে');
    }

    public function adjust(Request $request, Payslip $payslip): RedirectResponse
    {
        $validated = $request->validate([
            'other_addition' => ['nullable', 'numeric', 'min:0'],
            'other_deduction' => ['nullable', 'numeric', 'min:0'],
            'adjustment_note' => ['nullable', 'string', 'max:255'],
        ]);

        $this->payroll->adjust($payslip, $validated);

        return back()->with('status', $payslip->employee_name.' — পে-স্লিপ হালনাগাদ হয়েছে');
    }

    public function approve(PayrollRun $run): RedirectResponse
    {
        $this->payroll->approve($run);

        return back()->with('status', 'পে-রোল অনুমোদিত হয়েছে এবং হিসাবে পোস্ট হয়েছে');
    }

    public function reopen(PayrollRun $run): RedirectResponse
    {
        $this->payroll->reopen($run);

        return back()->with('status', 'পে-রোল আবার খসড়া করা হয়েছে');
    }

    public function destroy(PayrollRun $run): RedirectResponse
    {
        $this->payroll->delete($run);

        return redirect()->route('payroll.runs.index')->with('status', 'পে-রোল মুছে ফেলা হয়েছে');
    }

    public function payAll(Request $request, PayrollRun $run): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'integer'],
            'paid_on' => ['required', 'date'],
        ]);

        $count = $this->payroll->payAll($run, $this->companyAccount((int) $validated['account_id']), Carbon::parse($validated['paid_on']));

        return back()->with('status', "{$count} জনের বেতন পরিশোধ করা হয়েছে");
    }

    public function pay(Request $request, Payslip $payslip): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'paid_on' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $this->payroll->pay($payslip, $this->companyAccount((int) $validated['account_id']), (float) $validated['amount'], Carbon::parse($validated['paid_on']), $validated['note'] ?? null);

        return back()->with('status', $payslip->employee_name.' — বেতন পরিশোধ হয়েছে');
    }

    public function destroyPayment(PayrollPayment $payment): RedirectResponse
    {
        $this->payroll->deletePayment($payment);

        return back()->with('status', 'পেমেন্ট মুছে ফেলা হয়েছে');
    }

    public function payslip(Payslip $payslip): View
    {
        $payslip->load(['items', 'run', 'employee', 'payments.account:id,name']);

        return view('payroll::runs.payslip', [
            'payslips' => collect([$payslip]),
            'run' => $payslip->run,
            'shop' => Shop::find($payslip->shop_id),
        ]);
    }

    public function printAll(PayrollRun $run): View
    {
        return view('payroll::runs.payslip', [
            'payslips' => $run->payslips()->orderBy('employee_name')->with(['items', 'employee', 'payments.account:id,name'])->get(),
            'run' => $run,
            'shop' => Shop::find($run->shop_id),
        ]);
    }
}
