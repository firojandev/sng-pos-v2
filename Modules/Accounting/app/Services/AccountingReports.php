<?php

namespace Modules\Accounting\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Accounting\Models\JournalLine;
use Modules\Accounting\Models\LedgerAccount;

/**
 * Reports read from the ledger. Any report can be limited to one shop
 * (its lines) or run for the whole company.
 */
class AccountingReports
{
    public function __construct(private LedgerService $ledger, private ChartOfAccounts $chart) {}

    /**
     * Every postable account's balance, shown on its debit or credit side.
     *
     * @return array{rows: Collection<int, array{account: LedgerAccount, debit: float, credit: float}>, debit: float, credit: float}
     */
    public function trialBalance(int $companyId, Carbon $asOf, ?int $shopId = null): array
    {
        $totals = $this->ledger->totalsByAccount($companyId, null, $asOf, $shopId);

        $rows = $this->postableAccounts($companyId)
            ->map(function (LedgerAccount $account) use ($totals) {
                $row = $totals[$account->id] ?? null;
                $net = $row ? round((float) $row->debit - (float) $row->credit, 2) : 0.0;

                return ['account' => $account, 'debit' => max($net, 0), 'credit' => max(-$net, 0)];
            })
            ->filter(fn (array $row) => $row['debit'] != 0.0 || $row['credit'] != 0.0)
            ->values();

        return ['rows' => $rows, 'debit' => round($rows->sum('debit'), 2), 'credit' => round($rows->sum('credit'), 2)];
    }

    /**
     * An account's movements over a period with a running balance.
     *
     * @return array{opening: float, lines: Collection<int, array{line: JournalLine, balance: float}>, closing: float}
     */
    public function generalLedger(LedgerAccount $account, Carbon $from, Carbon $to, ?int $shopId = null): array
    {
        $before = $this->ledger->totalsByAccount($account->company_id, null, $from->copy()->subDay(), $shopId)[$account->id] ?? null;
        $balance = $before ? $account->normalBalance((float) $before->debit, (float) $before->credit) : 0.0;
        $opening = $balance;

        $lines = JournalLine::withoutGlobalScopes()
            ->select('journal_lines.*')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.ledger_account_id', $account->id)
            ->whereDate('journal_entries.entry_date', '>=', $from->toDateString())
            ->whereDate('journal_entries.entry_date', '<=', $to->toDateString())
            ->when($shopId, fn ($query) => $query->where('journal_lines.shop_id', $shopId))
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_lines.id')
            ->with(['entry', 'shop:id,name', 'party'])
            ->get()
            ->map(function (JournalLine $line) use ($account, &$balance) {
                $balance = round($balance + $account->normalBalance((float) $line->debit, (float) $line->credit), 2);

                return ['line' => $line, 'balance' => $balance];
            });

        return ['opening' => $opening, 'lines' => $lines, 'closing' => $balance];
    }

    /**
     * @return array{income: Collection<int, array{account: LedgerAccount, amount: float}>, expenses: Collection<int, array{account: LedgerAccount, amount: float}>, total_income: float, total_expenses: float, net: float}
     */
    public function profitAndLoss(int $companyId, Carbon $from, Carbon $to, ?int $shopId = null): array
    {
        $totals = $this->ledger->totalsByAccount($companyId, $from, $to, $shopId);
        $income = $this->section($companyId, 'income', $totals);
        $expenses = $this->section($companyId, 'expense', $totals);

        $totalIncome = round($income->sum('amount'), 2);
        $totalExpenses = round($expenses->sum('amount'), 2);

        return [
            'income' => $income,
            'expenses' => $expenses,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net' => round($totalIncome - $totalExpenses, 2),
        ];
    }

    /**
     * Assets = liabilities + equity. Profit not yet closed into retained
     * earnings is shown within equity so the sheet balances.
     *
     * @return array<string, mixed>
     */
    public function balanceSheet(int $companyId, Carbon $asOf, ?int $shopId = null): array
    {
        $totals = $this->ledger->totalsByAccount($companyId, null, $asOf, $shopId);
        $assets = $this->section($companyId, 'asset', $totals);
        $liabilities = $this->section($companyId, 'liability', $totals);
        $equity = $this->section($companyId, 'equity', $totals);

        $unclosedProfit = round(
            $this->section($companyId, 'income', $totals)->sum('amount') - $this->section($companyId, 'expense', $totals)->sum('amount'),
            2,
        );

        $totalAssets = round($assets->sum('amount'), 2);
        $totalLiabilities = round($liabilities->sum('amount'), 2);
        $totalEquity = round($equity->sum('amount') + $unclosedProfit, 2);

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'unclosed_profit' => $unclosedProfit,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }

    /**
     * @return Collection<int, LedgerAccount>
     */
    public function postableAccounts(int $companyId): Collection
    {
        $this->chart->ensureFor($companyId);

        return LedgerAccount::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('is_group', false)
            ->orderBy('code')
            ->get();
    }

    /**
     * Accounts of one type with their balance in the normal direction.
     *
     * @return Collection<int, array{account: LedgerAccount, amount: float}>
     */
    private function section(int $companyId, string $type, Collection $totals): Collection
    {
        return $this->postableAccounts($companyId)
            ->where('type', $type)
            ->map(function (LedgerAccount $account) use ($totals) {
                $row = $totals[$account->id] ?? null;

                return ['account' => $account, 'amount' => $row ? $account->normalBalance((float) $row->debit, (float) $row->credit) : 0.0];
            })
            ->filter(fn (array $row) => $row['amount'] != 0.0)
            ->values();
    }
}
