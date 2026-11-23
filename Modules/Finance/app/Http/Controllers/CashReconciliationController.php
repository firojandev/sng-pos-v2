<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Finance\Http\Requests\CloseCashSessionRequest;
use Modules\Finance\Http\Requests\OpenCashSessionRequest;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\CashSession;
use Modules\Finance\Services\CashReconciliationService;

class CashReconciliationController extends Controller
{
    public function __construct(
        protected CashReconciliationService $reconciliation
    ) {}

    public function index(Request $request): View
    {
        $drawers = $this->reconciliation->cashAccounts()->map(function (Account $account) {
            $openSession = $this->reconciliation->openSessionFor($account);

            return [
                'account' => $account,
                'openSession' => $openSession,
                'expected' => $openSession
                    ? $this->reconciliation->expectedClosingBalance($openSession, (float) $account->current_balance)
                    : $this->reconciliation->expectedOpeningBalance($account),
            ];
        });

        $accountFilter = $request->integer('account') ?: null;

        $sessions = CashSession::query()
            ->with(['account', 'opener', 'closer'])
            ->when($accountFilter, fn ($query) => $query->where('account_id', $accountFilter))
            ->latest('opened_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('finance::cash-reconciliation.index', compact('drawers', 'sessions', 'accountFilter'));
    }

    public function create(Account $account): View|RedirectResponse
    {
        $this->ensureCashAccount($account);

        if ($openSession = $this->reconciliation->openSessionFor($account)) {
            return redirect()->route('cash-reconciliation.show', $openSession);
        }

        return view('finance::cash-reconciliation.open', [
            'account' => $account,
            'expectedOpening' => $this->reconciliation->expectedOpeningBalance($account),
            'lastClosed' => $this->reconciliation->lastClosedSessionFor($account),
            'denominations' => CashReconciliationService::DENOMINATIONS,
        ]);
    }

    public function store(OpenCashSessionRequest $request, Account $account): RedirectResponse
    {
        $this->ensureCashAccount($account);

        $session = $this->reconciliation->open(
            $account,
            $request->user(),
            $request->openingCounts(),
            $request->otherAmount(),
            $request->validated('note'),
        );

        return redirect()->route('cash-reconciliation.show', $session)
            ->with('status', 'ক্যাশ সেশন চালু হয়েছে (Cash session opened)');
    }

    public function show(CashSession $cashSession): View
    {
        $cashSession->load(['account', 'opener', 'closer', 'counts']);

        if ($cashSession->isOpen()) {
            $systemBalance = (float) Account::withoutGlobalScopes()->whereKey($cashSession->account_id)->value('current_balance');
            $expectedClosing = $this->reconciliation->expectedClosingBalance($cashSession, $systemBalance);
            $summary = $this->reconciliation->movementSummary($cashSession, $systemBalance);
        } else {
            $expectedClosing = (float) $cashSession->expected_closing_balance;
            $summary = $cashSession->movement_summary ?? ['rows' => [], 'cash_in' => 0, 'cash_out' => 0, 'other_changes' => 0, 'net_change' => 0];
        }

        return view('finance::cash-reconciliation.show', [
            'session' => $cashSession,
            'expectedClosing' => $expectedClosing,
            'summary' => $summary,
            'denominations' => CashReconciliationService::DENOMINATIONS,
            'openingCounts' => $cashSession->counts->where('stage', 'opening')->values(),
            'closingCounts' => $cashSession->counts->where('stage', 'closing')->values(),
        ]);
    }

    public function close(CloseCashSessionRequest $request, CashSession $cashSession): RedirectResponse
    {
        $this->reconciliation->close(
            $cashSession,
            $request->user(),
            $request->closingCounts(),
            $request->otherAmount(),
            $request->validated('note'),
        );

        return redirect()->route('cash-reconciliation.show', $cashSession)
            ->with('status', 'ক্যাশ সেশন বন্ধ হয়েছে (Cash session closed)');
    }

    private function ensureCashAccount(Account $account): void
    {
        abort_unless($account->type === 'cash' && $account->status === 'active', 404);
    }
}
