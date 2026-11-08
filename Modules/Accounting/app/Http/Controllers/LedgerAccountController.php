<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Accounting\Http\Controllers\Concerns\WorksOnCurrentCompany;
use Modules\Accounting\Http\Requests\StoreLedgerAccountRequest;
use Modules\Accounting\Http\Requests\UpdateLedgerAccountRequest;
use Modules\Accounting\Models\LedgerAccount;
use Modules\Accounting\Services\LedgerService;

/**
 * The chart of accounts.
 */
class LedgerAccountController extends Controller
{
    use WorksOnCurrentCompany;

    public function index(LedgerService $ledger): View
    {
        $companyId = $this->companyId();
        $accounts = LedgerAccount::query()->orderBy('code')->get();
        $totals = $ledger->totalsByAccount($companyId);

        return view('accounting::accounts.index', [
            'roots' => $accounts->whereNull('parent_id')->values(),
            'childrenByParent' => $accounts->groupBy('parent_id'),
            'balances' => $accounts->mapWithKeys(fn (LedgerAccount $account) => [
                $account->id => isset($totals[$account->id])
                    ? $account->normalBalance((float) $totals[$account->id]->debit, (float) $totals[$account->id]->credit)
                    : 0.0,
            ]),
        ]);
    }

    public function create(): View
    {
        $this->companyId();

        return view('accounting::accounts.create', [
            'account' => new LedgerAccount,
            'groups' => LedgerAccount::query()->where('is_group', true)->orderBy('code')->get(),
        ]);
    }

    public function store(StoreLedgerAccountRequest $request): RedirectResponse
    {
        $parent = LedgerAccount::findOrFail($request->validated('parent_id'));

        LedgerAccount::create([
            ...$request->validated(),
            'company_id' => $this->companyId(),
            'type' => $parent->type,
        ]);

        return redirect()->route('ledger-accounts.index')->with('status', 'অ্যাকাউন্ট তৈরি করা হয়েছে');
    }

    public function edit(LedgerAccount $ledgerAccount): View
    {
        return view('accounting::accounts.edit', ['account' => $ledgerAccount]);
    }

    public function update(UpdateLedgerAccountRequest $request, LedgerAccount $ledgerAccount): RedirectResponse
    {
        $values = $request->validated();

        // Standard accounts keep their code and stay active; the app posts to them.
        if ($ledgerAccount->system_key && ! str_starts_with($ledgerAccount->system_key, 'money_account_')) {
            unset($values['code'], $values['is_active']);
        }

        $ledgerAccount->update($values);

        return redirect()->route('ledger-accounts.index')->with('status', 'অ্যাকাউন্ট হালনাগাদ করা হয়েছে');
    }
}
