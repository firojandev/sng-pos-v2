<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Modules\Accounting\Http\Controllers\Concerns\WorksOnCurrentCompany;
use Modules\Accounting\Models\FiscalYear;
use Modules\Accounting\Services\AccountingReports;

/**
 * Trial balance, general ledger, profit & loss and balance sheet.
 */
class AccountingReportController extends Controller
{
    use WorksOnCurrentCompany;

    public function __construct(private AccountingReports $reports) {}

    public function trialBalance(Request $request): View
    {
        $asOf = $this->asOf($request);

        return view('accounting::reports.trial-balance', [
            'report' => $this->reports->trialBalance($this->companyId(), $asOf, $this->shopFilter($request)),
            'asOf' => $asOf,
            'shops' => $this->companyShops(),
        ]);
    }

    public function generalLedger(Request $request): View
    {
        [$from, $to] = $this->period($request);
        $accounts = $this->reports->postableAccounts($this->companyId());
        $account = $request->integer('account_id') ? $accounts->firstWhere('id', $request->integer('account_id')) : null;

        return view('accounting::reports.general-ledger', [
            'accounts' => $accounts,
            'account' => $account,
            'report' => $account ? $this->reports->generalLedger($account, $from, $to, $this->shopFilter($request)) : null,
            'from' => $from,
            'to' => $to,
            'shops' => $this->companyShops(),
        ]);
    }

    public function profitAndLoss(Request $request): View
    {
        [$from, $to] = $this->period($request);

        return view('accounting::reports.profit-loss', [
            'report' => $this->reports->profitAndLoss($this->companyId(), $from, $to, $this->shopFilter($request)),
            'from' => $from,
            'to' => $to,
            'shops' => $this->companyShops(),
        ]);
    }

    public function balanceSheet(Request $request): View
    {
        $asOf = $this->asOf($request);

        return view('accounting::reports.balance-sheet', [
            'report' => $this->reports->balanceSheet($this->companyId(), $asOf, $this->shopFilter($request)),
            'asOf' => $asOf,
            'shops' => $this->companyShops(),
        ]);
    }

    private function asOf(Request $request): Carbon
    {
        return $request->filled('as_of') ? Carbon::parse($request->input('as_of'))->endOfDay() : now()->endOfDay();
    }

    /**
     * The requested period, defaulting to the current fiscal year to date.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function period(Request $request): array
    {
        $year = FiscalYear::query()
            ->whereDate('starts_on', '<=', now()->toDateString())
            ->whereDate('ends_on', '>=', now()->toDateString())
            ->first();

        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : ($year?->starts_on ?? now()->startOfYear());
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : now();

        return [$from->copy()->startOfDay(), $to->copy()->endOfDay()];
    }
}
