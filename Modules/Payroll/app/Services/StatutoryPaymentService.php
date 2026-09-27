<?php

namespace Modules\Payroll\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\Services\ChartOfAccounts;
use Modules\Accounting\Services\LedgerService;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\AccountTransactionService;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Models\StatutoryPayment;

/**
 * Paying over what payroll withheld: provident fund to the fund, income
 * tax to the government. A payment is recorded for a period (amounts taken
 * from the approved payrolls unless given), then marked paid from a bank
 * or cash account, which posts it to the ledger.
 */
class StatutoryPaymentService
{
    public function __construct(private AccountTransactionService $money) {}

    /**
     * What the approved payrolls of a period withheld.
     *
     * @return array{employee: float, employer: float, tax: float}
     */
    public function withheld(int $companyId, Carbon $from, Carbon $to): array
    {
        $totals = Payslip::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereHas('run', fn ($query) => $query->withoutGlobalScopes()
                ->where('status', 'approved')
                ->whereDate('month', '>=', $from->copy()->startOfMonth()->toDateString())
                ->whereDate('month', '<=', $to->copy()->endOfMonth()->toDateString()))
            ->selectRaw('COALESCE(SUM(pf_employee), 0) as employee, COALESCE(SUM(pf_employer), 0) as employer, COALESCE(SUM(tax), 0) as tax')
            ->first();

        return ['employee' => round((float) $totals->employee, 2), 'employer' => round((float) $totals->employer, 2), 'tax' => round((float) $totals->tax, 2)];
    }

    /**
     * The payables still owed, from the ledger.
     *
     * @return array{pf_employee: float, pf_employer: float, tax: float}
     */
    public function outstanding(int $companyId): array
    {
        $chart = app(ChartOfAccounts::class);
        $totals = app(LedgerService::class)->totalsByAccount($companyId);
        $balance = function (string $key) use ($chart, $companyId, $totals) {
            $account = $chart->account($companyId, $key);
            $row = $totals[$account->id] ?? null;

            return $row ? round((float) $row->credit - (float) $row->debit, 2) : 0.0;
        };

        return ['pf_employee' => $balance('pf_payable'), 'pf_employer' => $balance('pf_employer_payable'), 'tax' => $balance('tds_payable')];
    }

    /**
     * @param  array{type: string, period_from: string, period_to: string, employee_amount?: ?float, employer_amount?: ?float, amount?: ?float, payee?: ?string, note?: ?string}  $data
     */
    public function record(int $companyId, array $data): StatutoryPayment
    {
        $from = Carbon::parse($data['period_from'])->startOfMonth();
        $to = Carbon::parse($data['period_to'])->endOfMonth()->startOfDay();
        $withheld = $this->withheld($companyId, $from, $to);

        if ($data['type'] === 'pf') {
            $employee = round((float) ($data['employee_amount'] ?? $withheld['employee']), 2);
            $employer = round((float) ($data['employer_amount'] ?? $withheld['employer']), 2);
            $amount = $employee + $employer;
        } else {
            $employee = $employer = 0.0;
            $amount = round((float) ($data['amount'] ?? $withheld['tax']), 2);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'এই সময়ের জন্য পরিশোধের মতো কিছু নেই (Nothing to pay for this period)।']);
        }

        return StatutoryPayment::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'type' => $data['type'],
            'period_from' => $from->toDateString(),
            'period_to' => $to->toDateString(),
            'employee_amount' => $employee,
            'employer_amount' => $employer,
            'amount' => $amount,
            'payee' => $data['payee'] ?? null,
            'note' => $data['note'] ?? null,
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);
    }

    public function markPaid(StatutoryPayment $payment, Account $account, Carbon $date, ?string $reference): void
    {
        if ($payment->isPaid()) {
            throw ValidationException::withMessages(['status' => 'ইতিমধ্যে পরিশোধিত (Already paid)।']);
        }

        DB::transaction(function () use ($payment, $account, $date, $reference) {
            $payment->update([
                'account_id' => $account->id,
                'shop_id' => $account->shop_id,
                'payment_date' => $date->toDateString(),
                'reference' => $reference,
                'status' => 'paid',
                'paid_by' => Auth::id(),
            ]);

            $label = $payment->type === 'pf' ? 'প্রভিডেন্ট ফান্ড জমা (PF Payment)' : 'আয়কর জমা (Income Tax Payment)';
            $this->money->recordTransaction($account, 'out', (float) $payment->amount, 'statutory_payment', $payment, $label.($reference ? ' — '.$reference : ''), $date->copy()->setTimeFrom(now()));
        });
    }

    /**
     * Remove a payment; a paid one gives the money back to its account and
     * its ledger entry is reversed.
     */
    public function delete(StatutoryPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $this->money->deleteTransactionsFor($payment);
            $payment->delete();
        });
    }
}
