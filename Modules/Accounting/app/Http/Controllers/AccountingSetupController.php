<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Modules\Accounting\Http\Controllers\Concerns\WorksOnCurrentCompany;
use Modules\Accounting\Models\FiscalYear;
use Modules\Accounting\Services\ChartOfAccounts;
use Modules\Accounting\Services\MoneyOpeningBalances;
use Modules\Accounting\Services\OpeningBalanceService;
use Modules\Accounting\Services\Reconciliation;
use Modules\Company\Models\Company;

/**
 * Fiscal years and the opening balance that starts the ledger.
 */
class AccountingSetupController extends Controller
{
    use WorksOnCurrentCompany;

    public function index(OpeningBalanceService $opening, MoneyOpeningBalances $moneyOpening): View
    {
        $company = Company::findOrFail($this->companyId());
        $isPosted = $opening->isPosted($company->id);

        return view('accounting::setup.index', [
            'fiscalYears' => FiscalYear::query()->orderByDesc('starts_on')->get(),
            'openingPosted' => $isPosted,
            'openingLines' => $isPosted ? collect() : collect($opening->lines($company)),
            'reconciliation' => $isPosted ? app(Reconciliation::class)->compare($company->id) : [],
            'moneyAccounts' => $moneyOpening->accounts($company->id)->load('shop:id,name'),
        ]);
    }

    public function postOpening(Request $request, OpeningBalanceService $opening): RedirectResponse
    {
        $validated = $request->validate(['opening_date' => ['required', 'date']]);

        $entry = $opening->post(Company::findOrFail($this->companyId()), $validated['opening_date']);

        return redirect()->route('journal-entries.show', $entry)->with('status', 'প্রারম্ভিক ব্যালেন্স পোস্ট করা হয়েছে');
    }

    /**
     * Set the counted opening balance of cash and bank accounts.
     */
    public function saveMoneyOpening(Request $request, MoneyOpeningBalances $moneyOpening): RedirectResponse
    {
        $validated = $request->validate([
            'counted' => ['required', 'array'],
            'counted.*' => ['nullable', 'numeric'],
            'as_of' => ['required', 'date'],
        ]);

        $count = $moneyOpening->set($this->companyId(), $validated['counted'], Carbon::parse($validated['as_of']));

        return back()->with('status', "{$count}টি অ্যাকাউন্টের প্রারম্ভিক ব্যালেন্স সমন্বয় হয়েছে");
    }

    public function addNextYear(ChartOfAccounts $chart): RedirectResponse
    {
        $latest = FiscalYear::query()->orderByDesc('ends_on')->firstOrFail();

        $chart->ensureCurrentFiscalYear($this->companyId(), $latest->ends_on->copy()->addDay());

        return back()->with('status', 'পরবর্তী অর্থবছর যোগ করা হয়েছে');
    }

    public function toggleYear(FiscalYear $fiscalYear): RedirectResponse
    {
        $fiscalYear->update(['is_closed' => ! $fiscalYear->is_closed]);

        return back()->with('status', $fiscalYear->is_closed ? 'অর্থবছর বন্ধ করা হয়েছে' : 'অর্থবছর আবার খোলা হয়েছে');
    }
}
