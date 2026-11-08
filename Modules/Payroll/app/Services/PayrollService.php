<?php

namespace Modules\Payroll\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Employee\Models\Employee;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\AccountTransactionService;
use Modules\Payroll\Models\EmployeeLoan;
use Modules\Payroll\Models\EmployeeLoanRecovery;
use Modules\Payroll\Models\FinalSettlement;
use Modules\Payroll\Models\PayrollPayment;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Models\Payslip;

/**
 * A payroll run from draft to paid: generate the payslips, adjust and
 * recalculate while in draft, approve (which posts it to the ledger and
 * takes the loan installments), and pay from a cash, bank or mobile
 * banking account.
 */
class PayrollService
{
    public function __construct(
        private PayrollCalculator $calculator,
        private PayrollSetup $setup,
        private AccountTransactionService $money,
    ) {}

    public function create(int $shopId, Carbon $month, string $type = 'salary', ?string $title = null, ?string $note = null): PayrollRun
    {
        $month = $month->copy()->startOfMonth();
        $companyId = (int) DB::table('shops')->where('id', $shopId)->value('company_id');

        if ($type === 'salary' && PayrollRun::withoutGlobalScopes()->where('shop_id', $shopId)->where('type', 'salary')->whereDate('month', $month->toDateString())->exists()) {
            throw ValidationException::withMessages(['month' => 'এই মাসের বেতন ইতিমধ্যে তৈরি করা হয়েছে (Salary for this month has already been created)।']);
        }

        $run = PayrollRun::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'shop_id' => $shopId,
            'type' => $type,
            'title' => $type === 'bonus' ? ($title ?: 'উৎসব ভাতা (Festival Bonus)') : null,
            'month' => $month->toDateString(),
            'status' => 'draft',
            'note' => $note,
            'created_by' => Auth::id(),
        ]);

        $this->calculate($run);

        return $run->refresh();
    }

    /**
     * Work out every payslip of a draft run again, keeping the manual
     * additions and deductions. Staff who joined since are added.
     */
    public function calculate(PayrollRun $run): void
    {
        $this->ensureDraft($run);
        $settings = $this->setup->ensureFor($run->company_id);

        DB::transaction(function () use ($run, $settings) {
            $existing = $run->payslips()->get()->keyBy('employee_id');
            $monthStart = $run->month->copy()->startOfMonth();

            $employees = Employee::withoutGlobalScopes()
                ->where('company_id', $run->company_id)
                ->where('shop_id', $run->shop_id)
                ->where(fn ($query) => $query->where('status', 'active')->orWhereDate('separation_date', '>=', $monthStart->toDateString()))
                ->orWhereIn('id', $existing->keys())
                ->orderBy('name')
                ->get();

            $kept = [];

            foreach ($employees as $employee) {
                $payslip = $existing->get($employee->id);
                $result = $this->calculator->calculate($run, $employee, $settings, $payslip ? $payslip->only(['other_addition', 'other_deduction', 'adjustment_note']) : []);

                if (! $result) {
                    continue;
                }

                $payslip ??= new Payslip(['payroll_run_id' => $run->id, 'employee_id' => $employee->id]);
                $payslip->fill($result['attributes'] + ['company_id' => $run->company_id, 'shop_id' => $run->shop_id])->save();
                $payslip->items()->delete();
                $payslip->items()->createMany($result['items']);
                $kept[] = $payslip->id;
            }

            $run->payslips()->whereNotIn('id', $kept)->get()->each(function (Payslip $payslip) {
                $payslip->items()->delete();
                $payslip->delete();
            });

            $run->refreshTotals();
        });
    }

    /**
     * @param  array{other_addition?: float|string|null, other_deduction?: float|string|null, adjustment_note?: ?string}  $data
     */
    public function adjust(Payslip $payslip, array $data): void
    {
        $run = $payslip->run;
        $this->ensureDraft($run);

        $employee = Employee::withoutGlobalScopes()->findOrFail($payslip->employee_id);
        $result = $this->calculator->calculate($run, $employee, $this->setup->ensureFor($run->company_id), [
            'other_addition' => (float) ($data['other_addition'] ?? 0),
            'other_deduction' => (float) ($data['other_deduction'] ?? 0),
            'adjustment_note' => $data['adjustment_note'] ?? null,
        ]);

        DB::transaction(function () use ($payslip, $result, $run) {
            if (! $result) {
                return;
            }

            $payslip->fill($result['attributes'])->save();
            $payslip->items()->delete();
            $payslip->items()->createMany($result['items']);
            $run->refreshTotals();
        });

        if ($result && $result['attributes']['net_pay'] < 0) {
            throw ValidationException::withMessages(['other_deduction' => 'কর্তন বেতনের চেয়ে বেশি (Deductions exceed the pay)।']);
        }
    }

    public function approve(PayrollRun $run): void
    {
        $this->ensureDraft($run);

        if ($run->payslips()->where('net_pay', '<', 0)->exists()) {
            throw ValidationException::withMessages(['status' => 'কোনো কোনো পে-স্লিপে কর্তন বেতনের চেয়ে বেশি (Some payslips have deductions above the pay)।']);
        }

        DB::transaction(function () use ($run) {
            $recoveredOn = $run->month->copy()->endOfMonth()->toDateString();

            foreach ($run->payslips()->with('items')->get() as $payslip) {
                foreach ($payslip->items->whereNotNull('employee_loan_id') as $item) {
                    EmployeeLoanRecovery::create([
                        'employee_loan_id' => $item->employee_loan_id,
                        'amount' => $item->amount,
                        'recovered_on' => $recoveredOn,
                        'source_type' => $payslip->getMorphClass(),
                        'source_id' => $payslip->id,
                        'created_by' => Auth::id(),
                    ]);

                    $this->closeIfRepaid($item->employee_loan_id);
                }
            }

            $run->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now(), 'pay_date' => $run->pay_date ?? $recoveredOn]);
        });
    }

    /**
     * Back to draft (the ledger entry is reversed), while nothing is paid.
     */
    public function reopen(PayrollRun $run): void
    {
        if ($run->isDraft()) {
            return;
        }

        if ((float) $run->paid_total > 0) {
            throw ValidationException::withMessages(['status' => 'বেতন পরিশোধ শুরু হয়েছে; আগে পেমেন্টগুলো মুছুন (Payments have been made; delete them first)।']);
        }

        DB::transaction(function () use ($run) {
            $this->removeRecoveries(Payslip::class, $run->payslips()->pluck('id')->all());
            $run->update(['status' => 'draft', 'approved_by' => null, 'approved_at' => null]);
        });
    }

    public function delete(PayrollRun $run): void
    {
        $this->ensureDraft($run);

        DB::transaction(function () use ($run) {
            foreach ($run->payslips()->get() as $payslip) {
                $payslip->items()->delete();
                $payslip->delete();
            }

            $run->delete();
        });
    }

    /**
     * Pay a payslip or final settlement, in full or in part.
     */
    public function pay(Payslip|FinalSettlement $payable, Account $account, float $amount, Carbon $paidOn, ?string $note = null): PayrollPayment
    {
        if ($payable instanceof Payslip ? $payable->run->isDraft() : $payable->isDraft()) {
            throw ValidationException::withMessages(['amount' => 'অনুমোদনের আগে পরিশোধ করা যায় না (Approve it before paying)।']);
        }

        $amount = round($amount, 2);
        if ($amount <= 0 || $amount > $payable->due() + 0.001) {
            throw ValidationException::withMessages(['amount' => 'পরিমাণ বকেয়ার চেয়ে বেশি হতে পারে না (The amount can\'t exceed what is due: '.number_format($payable->due(), 2).')।']);
        }

        if ((int) DB::table('shops')->where('id', $account->shop_id)->value('company_id') !== (int) $payable->company_id) {
            throw ValidationException::withMessages(['account_id' => 'অ্যাকাউন্টটি এই কোম্পানির নয় (The account belongs to another company)।']);
        }

        return DB::transaction(function () use ($payable, $account, $amount, $paidOn, $note) {
            $payment = PayrollPayment::withoutGlobalScopes()->create([
                'company_id' => $payable->company_id,
                'shop_id' => $account->shop_id,
                'payable_type' => $payable->getMorphClass(),
                'payable_id' => $payable->id,
                'employee_id' => $payable->employee_id,
                'account_id' => $account->id,
                'amount' => $amount,
                'paid_on' => $paidOn->toDateString(),
                'note' => $note,
                'created_by' => Auth::id(),
            ]);

            $label = $payable instanceof Payslip ? 'বেতন (Salary) '.$payable->run->label() : 'চূড়ান্ত নিষ্পত্তি (Final Settlement)';
            $employeeName = Employee::withoutGlobalScopes()->whereKey($payable->employee_id)->value('name');
            $this->money->recordTransaction($account, 'out', $amount, 'salary', $payment, $label.' — '.$employeeName, $paidOn->copy()->setTimeFrom(now()));

            $this->refreshPaid($payable);

            return $payment;
        });
    }

    /**
     * Pay what is still due on every payslip of a run from one account.
     */
    public function payAll(PayrollRun $run, Account $account, Carbon $paidOn): int
    {
        $count = 0;

        foreach ($run->payslips()->get() as $payslip) {
            if ($payslip->due() > 0) {
                $this->pay($payslip, $account, $payslip->due(), $paidOn);
                $count++;
            }
        }

        return $count;
    }

    public function deletePayment(PayrollPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $this->money->deleteTransactionsFor($payment);
            $payable = $payment->payable;
            $payment->delete();

            if ($payable) {
                $this->refreshPaid($payable);
            }
        });
    }

    public function removeRecoveries(string $sourceClass, array $sourceIds): void
    {
        $recoveries = EmployeeLoanRecovery::where('source_type', (new $sourceClass)->getMorphClass())->whereIn('source_id', $sourceIds)->get();

        foreach ($recoveries as $recovery) {
            $recovery->delete();
            EmployeeLoan::withoutGlobalScopes()->whereKey($recovery->employee_loan_id)->update(['status' => 'active']);
        }
    }

    public function closeIfRepaid(int $loanId): void
    {
        $loan = EmployeeLoan::withoutGlobalScopes()->find($loanId);

        if ($loan && $loan->balance() <= 0.001) {
            $loan->update(['status' => 'closed']);
        }
    }

    private function refreshPaid(Model $payable): void
    {
        $payable->forceFill(['paid_amount' => round((float) $payable->payments()->sum('amount'), 2)])->saveQuietly();

        if ($payable instanceof Payslip) {
            $payable->run->refreshTotals();
        }
    }

    private function ensureDraft(PayrollRun $run): void
    {
        if (! $run->isDraft()) {
            throw ValidationException::withMessages(['status' => 'অনুমোদিত পে-রোল পরিবর্তন করা যায় না; আগে খুলুন (An approved payroll can\'t be changed; reopen it first)।']);
        }
    }
}
