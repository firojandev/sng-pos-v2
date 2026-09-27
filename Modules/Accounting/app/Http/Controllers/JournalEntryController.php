<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Accounting\Http\Controllers\Concerns\WorksOnCurrentCompany;
use Modules\Accounting\Http\Requests\StoreJournalEntryRequest;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\LedgerAccount;
use Modules\Accounting\Services\LedgerService;
use Modules\Core\Support\TenantContext;

/**
 * Journal entries: the list, manual entries and reversals.
 */
class JournalEntryController extends Controller
{
    use WorksOnCurrentCompany;

    public function index(Request $request): View
    {
        $this->companyId();
        $shopId = $this->shopFilter($request);

        $entries = JournalEntry::query()
            ->with(['lines', 'shop:id,name'])
            ->when($request->filled('from'), fn ($query) => $query->whereDate('entry_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('entry_date', '<=', $request->date('to')))
            ->when($shopId, fn ($query) => $query->whereHas('lines', fn ($lines) => $lines->where('shop_id', $shopId)))
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($match) => $match
                ->where('number', 'like', '%'.$request->string('search').'%')
                ->orWhere('narration', 'like', '%'.$request->string('search').'%')
                ->orWhere('reference', 'like', '%'.$request->string('search').'%')))
            ->latest('entry_date')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('accounting::journals.index', ['entries' => $entries, 'shops' => $this->companyShops()]);
    }

    public function create(): View
    {
        $this->companyId();

        return view('accounting::journals.create', [
            'accounts' => LedgerAccount::query()
                ->where('is_group', false)
                ->where('is_active', true)
                ->where('allow_manual_posting', true)
                ->orderBy('code')
                ->get(),
            // In the company workspace the entry may be for one of the shops.
            'shops' => app(TenantContext::class)->shopId() ? collect() : $this->companyShops()->pluck('name', 'id'),
        ]);
    }

    public function store(StoreJournalEntryRequest $request, LedgerService $ledger): RedirectResponse
    {
        $shopId = app(TenantContext::class)->shopId() ?? $request->validated('shop_id');

        $entry = $ledger->post(
            $this->companyId(),
            $request->validated('entry_date'),
            collect($request->validated('lines'))->map(fn (array $line) => [
                'account' => (int) $line['ledger_account_id'],
                'debit' => (float) ($line['debit'] ?? 0),
                'credit' => (float) ($line['credit'] ?? 0),
                'memo' => $line['memo'] ?? null,
            ])->all(),
            $request->validated('narration'),
            $shopId,
            null,
            $request->validated('reference'),
        );

        return redirect()->route('journal-entries.show', $entry)->with('status', "জার্নাল এন্ট্রি {$entry->number} পোস্ট করা হয়েছে");
    }

    public function show(JournalEntry $journalEntry): View
    {
        $journalEntry->load(['lines.account', 'lines.shop:id,name', 'lines.party', 'reversal', 'reversalOf', 'creator:id,name']);

        return view('accounting::journals.show', ['entry' => $journalEntry]);
    }

    public function reverse(JournalEntry $journalEntry, LedgerService $ledger): RedirectResponse
    {
        $reversal = $ledger->reverse($journalEntry);

        return redirect()->route('journal-entries.show', $reversal)->with('status', "{$journalEntry->number} রিভার্স করা হয়েছে");
    }
}
