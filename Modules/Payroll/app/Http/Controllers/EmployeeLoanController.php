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
use Modules\Payroll\Models\EmployeeLoan;
use Modules\Payroll\Services\LoanService;

/**
 * Salary advances and loans to the staff of the current shop.
 */
class EmployeeLoanController extends Controller
{
    use WorksOnPayroll;

    public function __construct(private LoanService $loans) {}

    public function index(Request $request): View
    {
        $employeeIds = Employee::workingAtShop()->pluck('id');

        $loans = EmployeeLoan::whereIn('employee_id', $employeeIds)
            ->when($request->input('status', 'active') !== 'all', fn ($query) => $query->where('status', $request->input('status', 'active')))
            ->with(['employee:id,name,employee_code', 'account:id,name'])
            ->withSum('recoveries', 'amount')
            ->latest('issued_on')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('payroll::loans.index', [
            'loans' => $loans,
            'employees' => $this->activeEmployees(),
            'accounts' => $this->moneyAccounts(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', Rule::in(Employee::pluck('id')->all())],
            'type' => ['required', Rule::in(['advance', 'loan'])],
            'amount' => ['required', 'numeric', 'gt:0'],
            'installment' => ['required', 'numeric', 'gt:0'],
            'issued_on' => ['required', 'date'],
            'deduct_from' => ['required', 'date_format:Y-m'],
            'account_id' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $this->loans->issue(
            Employee::findOrFail($validated['employee_id']),
            $this->companyAccount((int) $validated['account_id']),
            ['deduct_from' => $validated['deduct_from'].'-01'] + $validated,
        );

        return back()->with('status', 'অগ্রিম/ঋণ দেওয়া হয়েছে');
    }

    public function repay(Request $request, EmployeeLoan $loan): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'account_id' => ['required', 'integer'],
            'recovered_on' => ['required', 'date'],
        ]);

        $this->loans->repay($loan, $this->companyAccount((int) $validated['account_id']), (float) $validated['amount'], Carbon::parse($validated['recovered_on']));

        return back()->with('status', 'ফেরত জমা হয়েছে');
    }

    public function destroy(EmployeeLoan $loan): RedirectResponse
    {
        $this->loans->delete($loan);

        return back()->with('status', 'অগ্রিম/ঋণ মুছে ফেলা হয়েছে');
    }
}
