<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\Employee\Models\Employee;
use Modules\Employee\Services\SelfService;
use Modules\Payroll\Models\PayrollPayment;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Services\SalaryStructure;
use Modules\Shop\Models\Shop;

/**
 * My Salary: a user linked to an employee sees their own salary — its
 * breakdown and their employment history (joining to current position) —
 * and their approved payslips with what was paid,
 * each printable (or saved as PDF). Draft payroll runs are not shown.
 */
class MySalaryController extends Controller
{
    public function __construct(private SelfService $selfService, private SalaryStructure $structure) {}

    public function index(): View
    {
        $employee = $this->employee();
        $gross = $this->structure->salaryOn($employee, now());

        $payslips = $this->approvedPayslips($employee)
            ->with(['run' => fn ($query) => $query->withoutGlobalScopes(), 'payments' => fn ($query) => $query->withoutGlobalScopes()->with(['account' => fn ($query) => $query->withoutGlobalScopes()->select('id', 'name')])->latest('paid_on')])
            ->get();

        return view('payroll::my-salary.index', [
            'employee' => $employee,
            'gross' => $gross,
            'breakdown' => $this->structure->breakdown($employee, $gross),
            'history' => $this->structure->employmentHistory($employee),
            'payslips' => $payslips,
            'payments' => $payslips->flatMap(fn (Payslip $payslip) => $payslip->payments->each(fn (PayrollPayment $payment) => $payment->setRelation('payslip', $payslip)))
                ->sortByDesc(fn (PayrollPayment $payment) => $payment->paid_on?->timestamp)
                ->values(),
        ]);
    }

    /**
     * One of the user's own approved payslips, ready to print or save as PDF.
     */
    public function payslip(int $payslip): View
    {
        $payslip = $this->approvedPayslips($this->employee())
            ->with(['items', 'run' => fn ($query) => $query->withoutGlobalScopes(), 'employee' => fn ($query) => $query->withoutGlobalScopes(), 'payments' => fn ($query) => $query->withoutGlobalScopes()->with(['account' => fn ($query) => $query->withoutGlobalScopes()->select('id', 'name')])])
            ->findOrFail($payslip);

        return view('payroll::runs.payslip', [
            'payslips' => collect([$payslip]),
            'run' => $payslip->run,
            'shop' => Shop::find($payslip->shop_id),
        ]);
    }

    private function employee(): Employee
    {
        $employee = $this->selfService->employeeFor(Auth::user());
        abort_unless($employee, 403, 'আপনার ইউজার কোনো কর্মচারীর সাথে যুক্ত নয় (Your user is not linked to an employee)।');

        return $employee;
    }

    /**
     * @return Builder<Payslip>
     */
    private function approvedPayslips(Employee $employee)
    {
        return Payslip::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->whereHas('run', fn ($query) => $query->withoutGlobalScopes()->where('status', '!=', 'draft'))
            ->latest('id');
    }
}
