<?php

namespace Modules\Payroll\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Employee\Models\Employee;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\AccountTransactionService;
use Modules\Payroll\Models\EmployeeLoan;
use Modules\Payroll\Models\EmployeeLoanRecovery;

/**
 * Salary advances and loans to staff: paid out of a money account and
 * recovered from the salary, or repaid in cash.
 */
class LoanService
{
    public function __construct(private AccountTransactionService $money, private PayrollService $payroll) {}

    /**
     * @param  array{type: string, amount: float|string, installment: float|string, issued_on: string, deduct_from: string, note?: ?string}  $data
     */
    public function issue(Employee $employee, Account $account, array $data): EmployeeLoan
    {
        return DB::transaction(function () use ($employee, $account, $data) {
            $issuedOn = Carbon::parse($data['issued_on']);

            $loan = EmployeeLoan::withoutGlobalScopes()->create([
                'company_id' => $employee->company_id,
                'shop_id' => $account->shop_id,
                'employee_id' => $employee->id,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'installment' => min((float) $data['installment'], (float) $data['amount']),
                'issued_on' => $issuedOn->toDateString(),
                'deduct_from' => Carbon::parse($data['deduct_from'])->startOfMonth()->toDateString(),
                'account_id' => $account->id,
                'note' => $data['note'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $label = $loan->type === 'loan' ? 'কর্মচারী ঋণ (Employee Loan)' : 'বেতন অগ্রিম (Salary Advance)';
            $this->money->recordTransaction($account, 'out', (float) $loan->amount, 'employee_advance', $loan, $label.' — '.$employee->name, $issuedOn->copy()->setTimeFrom(now()));

            return $loan;
        });
    }

    public function repay(EmployeeLoan $loan, Account $account, float $amount, Carbon $date, ?string $note = null): EmployeeLoanRecovery
    {
        $amount = round($amount, 2);

        if ($amount <= 0 || $amount > $loan->balance() + 0.001) {
            throw ValidationException::withMessages(['amount' => 'পরিমাণ বাকির চেয়ে বেশি হতে পারে না (The amount can\'t exceed the balance: '.number_format($loan->balance(), 2).')।']);
        }

        return DB::transaction(function () use ($loan, $account, $amount, $date, $note) {
            $recovery = EmployeeLoanRecovery::create([
                'employee_loan_id' => $loan->id,
                'amount' => $amount,
                'recovered_on' => $date->toDateString(),
                'account_id' => $account->id,
                'note' => $note,
                'created_by' => Auth::id(),
            ]);

            $employeeName = Employee::withoutGlobalScopes()->whereKey($loan->employee_id)->value('name');
            $this->money->recordTransaction($account, 'in', $amount, 'employee_advance_repaid', $recovery, 'অগ্রিম/ঋণ ফেরত (Advance Repaid) — '.$employeeName, $date->copy()->setTimeFrom(now()));
            $this->payroll->closeIfRepaid($loan->id);

            return $recovery;
        });
    }

    /**
     * Only a loan nothing has been recovered from can be deleted; the money
     * goes back into the account it was paid from.
     */
    public function delete(EmployeeLoan $loan): void
    {
        if ($loan->recoveries()->exists()) {
            throw ValidationException::withMessages(['loan' => 'কিস্তি আদায় শুরু হয়েছে, মুছে ফেলা যাবে না (Installments have been recovered; it can\'t be deleted)।']);
        }

        DB::transaction(function () use ($loan) {
            $this->money->deleteTransactionsFor($loan);
            $loan->delete();
        });
    }
}
