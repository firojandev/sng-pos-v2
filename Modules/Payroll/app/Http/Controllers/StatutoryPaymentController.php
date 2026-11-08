<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Payroll\Http\Controllers\Concerns\WorksOnPayroll;
use Modules\Payroll\Models\StatutoryPayment;
use Modules\Payroll\Services\StatutoryPaymentService;

/**
 * Statutory payments: provident fund and income tax paid over.
 */
class StatutoryPaymentController extends Controller
{
    use WorksOnPayroll;

    public function __construct(private StatutoryPaymentService $statutory) {}

    public function index(Request $request): View
    {
        $companyId = $this->companyId();
        $from = Carbon::parse(($request->input('from') ?: now()->subMonthNoOverflow()->format('Y-m')).'-01');
        $to = Carbon::parse(($request->input('to') ?: $from->format('Y-m')).'-01');

        return view('payroll::statutory.index', [
            'payments' => StatutoryPayment::with('account:id,name')->latest('period_from')->latest('id')->paginate(25),
            'outstanding' => $this->statutory->outstanding($companyId),
            'withheld' => $this->statutory->withheld($companyId, $from, $to),
            'from' => $from,
            'to' => $to,
            'accounts' => $this->moneyAccounts(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['pf', 'tax'])],
            'period_from' => ['required', 'date_format:Y-m'],
            'period_to' => ['required', 'date_format:Y-m'],
            'employee_amount' => ['nullable', 'numeric', 'min:0'],
            'employer_amount' => ['nullable', 'numeric', 'min:0'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'payee' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['period_from'] .= '-01';
        $validated['period_to'] .= '-01';

        if (Carbon::parse($validated['period_to'])->lt(Carbon::parse($validated['period_from']))) {
            return back()->withErrors(['period_to' => 'শেষ মাস শুরুর আগে হতে পারে না (The period can\'t end before it starts)।'])->withInput();
        }

        $this->statutory->record($this->companyId(), array_filter($validated, fn ($value) => $value !== null && $value !== ''));

        return back()->with('status', 'পরিশোধের তথ্য যোগ হয়েছে');
    }

    public function pay(Request $request, StatutoryPayment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'integer'],
            'payment_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $this->statutory->markPaid($payment, $this->companyAccount((int) $validated['account_id']), Carbon::parse($validated['payment_date']), $validated['reference'] ?? null);

        return back()->with('status', 'পরিশোধিত হয়েছে এবং হিসাবে পোস্ট হয়েছে');
    }

    public function destroy(StatutoryPayment $payment): RedirectResponse
    {
        $this->statutory->delete($payment);

        return back()->with('status', 'মুছে ফেলা হয়েছে');
    }
}
