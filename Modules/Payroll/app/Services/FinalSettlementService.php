<?php

namespace Modules\Payroll\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\LeaveType;
use Modules\Employee\Services\LeaveService;
use Modules\Payroll\Models\EmployeeLoan;
use Modules\Payroll\Models\EmployeeLoanRecovery;
use Modules\Payroll\Models\FinalSettlement;
use Modules\Payroll\Models\FinalSettlementItem;
use Modules\Payroll\Models\Payslip;

/**
 * Final settlement when an employee leaves. The suggested amounts follow
 * the Bangladesh Labour Act 2006 and can all be changed before finalizing:
 *
 * - Resignation (s.27): 14 days' basic per year of service after 5 years,
 *   30 days' after 10 years.
 * - Termination by the employer (s.26), retirement (s.28): 30 days' basic
 *   per year.
 * - Dismissal for misconduct (s.23): 14 days' basic per year after 1 year.
 * - Death (s.19): 30 days' basic per year after 2 years.
 * - Unused leave of the encashable types (earned leave, s.11) at a day's
 *   gross wage (gross ÷ 30).
 * - The provident fund: the employee's own share always, the employer's
 *   share after the vesting years (not on dismissal).
 * - Loans and advances still owed are deducted.
 */
class FinalSettlementService
{
    public function __construct(
        private SalaryStructure $structure,
        private LeaveService $leave,
        private PayrollSetup $setup,
        private PayrollService $payroll,
    ) {}

    public function prepare(Employee $employee, string $separationType, Carbon $separationDate, ?string $note = null): FinalSettlement
    {
        if (FinalSettlement::withoutGlobalScopes()->where('employee_id', $employee->id)->exists()) {
            throw ValidationException::withMessages(['employee_id' => 'এই কর্মচারীর নিষ্পত্তি ইতিমধ্যে তৈরি হয়েছে (A settlement already exists for this employee)।']);
        }

        $settings = $this->setup->ensureFor($employee->company_id);
        $separationDate = $separationDate->copy()->startOfDay();
        $joined = ($employee->joining_date ?? $employee->created_at ?? $separationDate)->copy()->startOfDay();
        $years = (int) floor($joined->diffInYears($separationDate));
        $gross = $this->structure->salaryOn($employee, $separationDate);
        $basic = $this->structure->breakdown($employee, $gross)['basic'];
        $dailyBasic = $basic / 30;

        $benefitDays = match ($separationType) {
            'resignation' => $years >= 10 ? 30 : ($years >= 5 ? 14 : 0),
            'termination', 'retirement' => 30,
            'dismissal' => $years >= 1 ? 14 : 0,
            'death' => $years >= 2 ? 30 : 0,
            default => 0,
        };

        $pf = Payslip::withoutGlobalScopes()
            ->where('employee_id', $employee->id)
            ->whereHas('run', fn ($query) => $query->withoutGlobalScopes()->where('status', 'approved'))
            ->selectRaw('COALESCE(SUM(pf_employee), 0) as own, COALESCE(SUM(pf_employer), 0) as employer')
            ->first();
        $employerVests = $years >= $settings->pf_employer_vesting_years && $separationType !== 'dismissal';

        $items = [
            ['earning', 'BENEFIT', "চাকরির সুবিধা (Service Benefit) — {$benefitDays} দিন × {$years} বছর", $benefitDays * $years * $dailyBasic],
            ['earning', 'LEAVE', 'অভোগকৃত ছুটির নগদায়ন (Leave Encashment) — '.$this->unusedEncashableLeave($employee, $separationDate).' দিন', $this->unusedEncashableLeave($employee, $separationDate) * $gross / 30],
            ['earning', 'NOTICE_PAY', 'নোটিশের পরিবর্তে মজুরি (Pay in Lieu of Notice)', 0],
            ['earning', 'PF_OWN', 'প্রভিডেন্ট ফান্ড — নিজের অংশ (PF Own Contribution)', (float) $pf->own],
            ['earning', 'PF_EMPLOYER', 'প্রভিডেন্ট ফান্ড — মালিকের অংশ (PF Employer Contribution)', $employerVests ? (float) $pf->employer : 0],
            ['earning', 'OTHER_ADD', 'অন্যান্য প্রাপ্য (Other Payable)', 0],
            ['deduction', 'NOTICE_DED', 'নোটিশ না দেওয়ার কর্তন (Notice Shortfall)', 0],
            ['deduction', 'OTHER_DED', 'অন্যান্য কর্তন (Other Deduction)', 0],
        ];

        return DB::transaction(function () use ($employee, $separationType, $separationDate, $note, $joined, $gross, $basic, $pf, $employerVests, $items) {
            $settlement = FinalSettlement::withoutGlobalScopes()->create([
                'company_id' => $employee->company_id,
                'shop_id' => $employee->shop_id,
                'employee_id' => $employee->id,
                'separation_type' => $separationType,
                'separation_date' => $separationDate->toDateString(),
                'service_days' => (int) $joined->diffInDays($separationDate) + 1,
                'last_salary' => $gross,
                'last_basic' => $basic,
                'pf_forfeited' => $employerVests ? 0 : round((float) $pf->employer, 2),
                'status' => 'draft',
                'note' => $note,
                'created_by' => Auth::id(),
            ]);

            foreach ($items as $index => [$type, $code, $name, $amount]) {
                $settlement->items()->create(['type' => $type, 'code' => $code, 'name' => $name, 'amount' => round($amount, 2), 'sort_order' => $index + 1]);
            }

            $loans = EmployeeLoan::withoutGlobalScopes()->where('employee_id', $employee->id)->where('status', 'active')->get();
            foreach ($loans as $loan) {
                if ($loan->balance() > 0) {
                    $settlement->items()->create([
                        'type' => 'deduction',
                        'code' => 'LOAN',
                        'name' => ($loan->type === 'loan' ? 'ঋণের বাকি (Loan Balance)' : 'অগ্রিমের বাকি (Advance Balance)').' #'.$loan->id,
                        'amount' => $loan->balance(),
                        'employee_loan_id' => $loan->id,
                        'sort_order' => 20 + $loan->id,
                    ]);
                }
            }

            $this->refreshTotals($settlement);

            return $settlement->refresh();
        });
    }

    /**
     * @param  array<int|string, float|string|null>  $amounts  item id => amount
     */
    public function update(FinalSettlement $settlement, array $amounts, ?string $note = null): void
    {
        $this->ensureDraft($settlement);

        DB::transaction(function () use ($settlement, $amounts, $note) {
            foreach ($settlement->items()->get() as $item) {
                if (array_key_exists($item->id, $amounts)) {
                    $amount = round(max((float) $amounts[$item->id], 0), 2);

                    if ($item->employee_loan_id) {
                        $amount = min($amount, EmployeeLoan::withoutGlobalScopes()->find($item->employee_loan_id)?->balance() ?? 0);
                    }

                    $item->update(['amount' => $amount]);
                }
            }

            $settlement->update(['note' => $note]);
            $this->refreshTotals($settlement);
        });
    }

    public function finalize(FinalSettlement $settlement): void
    {
        $this->ensureDraft($settlement);

        if ((float) $settlement->net_pay < 0) {
            throw ValidationException::withMessages(['net_pay' => 'কর্তন প্রাপ্যের চেয়ে বেশি; বাকি অগ্রিম নগদে আদায় করুন (Deductions exceed what is payable; collect the rest of the advance in cash)।']);
        }

        DB::transaction(function () use ($settlement) {
            foreach ($settlement->items()->whereNotNull('employee_loan_id')->where('amount', '>', 0)->get() as $item) {
                EmployeeLoanRecovery::create([
                    'employee_loan_id' => $item->employee_loan_id,
                    'amount' => $item->amount,
                    'recovered_on' => $settlement->separation_date->toDateString(),
                    'source_type' => $settlement->getMorphClass(),
                    'source_id' => $settlement->id,
                    'created_by' => Auth::id(),
                ]);

                $this->payroll->closeIfRepaid($item->employee_loan_id);
            }

            $employee = Employee::withoutGlobalScopes()->findOrFail($settlement->employee_id);
            $employee->update([
                'status' => match ($settlement->separation_type) {
                    'resignation', 'retirement' => 'resigned',
                    'death' => 'inactive',
                    default => 'terminated',
                },
                'separation_date' => $settlement->separation_date->toDateString(),
                'separation_reason' => $employee->separation_reason ?: FinalSettlement::separationLabels()[$settlement->separation_type]['en'],
            ]);

            $settlement->update(['status' => 'finalized', 'finalized_by' => Auth::id(), 'finalized_at' => now()]);
        });
    }

    /**
     * Back to draft (the ledger entry is reversed), while nothing is paid.
     */
    public function reopen(FinalSettlement $settlement): void
    {
        if ((float) $settlement->paid_amount > 0) {
            throw ValidationException::withMessages(['status' => 'পরিশোধ শুরু হয়েছে; আগে পেমেন্টগুলো মুছুন (Payments have been made; delete them first)।']);
        }

        DB::transaction(function () use ($settlement) {
            $this->payroll->removeRecoveries(FinalSettlement::class, [$settlement->id]);
            $settlement->update(['status' => 'draft', 'finalized_by' => null, 'finalized_at' => null]);
        });
    }

    public function delete(FinalSettlement $settlement): void
    {
        $this->ensureDraft($settlement);

        DB::transaction(function () use ($settlement) {
            $settlement->items()->delete();
            $settlement->delete();
        });
    }

    /**
     * Unused days of the leave types that are paid out on leaving.
     */
    private function unusedEncashableLeave(Employee $employee, Carbon $date): int
    {
        return (int) LeaveType::withoutGlobalScopes()
            ->where('company_id', $employee->company_id)
            ->where('is_encashable', true)
            ->get()
            ->sum(fn (LeaveType $type) => max($this->leave->balance($employee, $type, $date->year, $date)['available'], 0));
    }

    private function refreshTotals(FinalSettlement $settlement): void
    {
        $items = FinalSettlementItem::where('final_settlement_id', $settlement->id)->get();
        $earnings = round((float) $items->where('type', 'earning')->sum('amount'), 2);
        $deductions = round((float) $items->where('type', 'deduction')->sum('amount'), 2);

        $settlement->forceFill(['earnings_total' => $earnings, 'deductions_total' => $deductions, 'net_pay' => round($earnings - $deductions, 2)])->save();
    }

    private function ensureDraft(FinalSettlement $settlement): void
    {
        if (! $settlement->isDraft()) {
            throw ValidationException::withMessages(['status' => 'চূড়ান্ত নিষ্পত্তি পরিবর্তন করা যায় না; আগে খুলুন (A finalized settlement can\'t be changed; reopen it first)।']);
        }
    }
}
