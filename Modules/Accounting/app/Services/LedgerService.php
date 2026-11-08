<?php

namespace Modules\Accounting\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\Models\FiscalYear;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalLine;
use Modules\Accounting\Models\LedgerAccount;

/**
 * Posts balanced journal entries to a company's ledger and reads balances.
 */
class LedgerService
{
    public function __construct(private ChartOfAccounts $chart) {}

    /**
     * Post an entry. Each line: ['account' => LedgerAccount|int, 'debit' => x,
     * 'credit' => y, 'shop_id' => ?int, 'party' => ?Model, 'memo' => ?string].
     *
     * @param  list<array<string, mixed>>  $lines
     */
    public function post(
        int $companyId,
        Carbon|string $date,
        array $lines,
        ?string $narration = null,
        ?int $shopId = null,
        ?Model $source = null,
        ?string $reference = null,
        ?int $reversalOfId = null,
    ): JournalEntry {
        $date = Carbon::parse($date)->startOfDay();
        $this->chart->ensureFor($companyId);
        $lines = $this->validatedLines($companyId, $lines, $shopId);
        $this->ensureOpenPeriod($companyId, $date);

        return DB::transaction(function () use ($companyId, $date, $lines, $narration, $shopId, $source, $reference, $reversalOfId) {
            $entry = JournalEntry::withoutGlobalScopes()->create([
                'company_id' => $companyId,
                'shop_id' => $shopId,
                'number' => $this->nextNumber($companyId),
                'entry_date' => $date->toDateString(),
                'narration' => $narration,
                'reference' => $reference,
                'source_type' => $source ? $source::class : null,
                'source_id' => $source?->getKey(),
                'reversal_of_id' => $reversalOfId,
                'created_by' => Auth::id(),
            ]);

            foreach ($lines as $line) {
                JournalLine::withoutGlobalScopes()->create([
                    'journal_entry_id' => $entry->id,
                    'company_id' => $companyId,
                    'ledger_account_id' => $line['account_id'],
                    'shop_id' => $line['shop_id'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'party_type' => $line['party'] ? $line['party']::class : null,
                    'party_id' => $line['party']?->getKey(),
                    'memo' => $line['memo'],
                ]);
            }

            return $entry->load('lines');
        });
    }

    /**
     * Cancel an entry by posting its mirror image.
     */
    public function reverse(JournalEntry $entry, Carbon|string|null $date = null, ?string $narration = null): JournalEntry
    {
        if ($entry->isReversed() || $entry->reversal_of_id) {
            throw ValidationException::withMessages(['entry' => 'এই এন্ট্রি ইতিমধ্যে রিভার্স করা হয়েছে (This entry is already reversed or is a reversal)।']);
        }

        return DB::transaction(function () use ($entry, $date, $narration) {
            $lines = $entry->lines()->get()->map(fn (JournalLine $line) => [
                'account' => $line->ledger_account_id,
                'debit' => (float) $line->credit,
                'credit' => (float) $line->debit,
                'shop_id' => $line->shop_id,
                'party' => $line->party_type ? $line->party : null,
                'memo' => $line->memo,
            ])->all();

            $reversal = $this->post(
                $entry->company_id,
                $date ?? now(),
                $lines,
                $narration ?? 'Reversal of '.$entry->number,
                $entry->shop_id,
                $entry->source,
                $entry->number,
                $entry->id,
            );

            $entry->update(['reversed_at' => now()]);

            return $reversal;
        });
    }

    /**
     * Debit and credit totals per ledger account.
     *
     * @return Collection<int, object{ledger_account_id: int, debit: float, credit: float}>
     */
    public function totalsByAccount(int $companyId, ?Carbon $from = null, ?Carbon $to = null, ?int $shopId = null): Collection
    {
        return JournalLine::withoutGlobalScopes()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.company_id', $companyId)
            ->when($from, fn ($query) => $query->whereDate('journal_entries.entry_date', '>=', $from->toDateString()))
            ->when($to, fn ($query) => $query->whereDate('journal_entries.entry_date', '<=', $to->toDateString()))
            ->when($shopId, fn ($query) => $query->where('journal_lines.shop_id', $shopId))
            ->groupBy('journal_lines.ledger_account_id')
            ->selectRaw('journal_lines.ledger_account_id, SUM(journal_lines.debit) as debit, SUM(journal_lines.credit) as credit')
            ->get()
            ->keyBy('ledger_account_id');
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array{account_id: int, debit: float, credit: float, shop_id: ?int, party: ?Model, memo: ?string}>
     */
    private function validatedLines(int $companyId, array $lines, ?int $defaultShopId): array
    {
        $fail = fn (string $message) => throw ValidationException::withMessages(['lines' => $message]);

        $normalised = [];
        foreach ($lines as $line) {
            $debit = round((float) ($line['debit'] ?? 0), 2);
            $credit = round((float) ($line['credit'] ?? 0), 2);

            if ($debit == 0.0 && $credit == 0.0) {
                continue;
            }

            if ($debit < 0 || $credit < 0 || ($debit > 0 && $credit > 0)) {
                $fail('প্রতিটি লাইনে হয় ডেবিট নয়তো ক্রেডিট থাকবে (Each line is either a debit or a credit)।');
            }

            $accountId = $line['account'] instanceof LedgerAccount ? $line['account']->id : (int) $line['account'];

            $normalised[] = [
                'account_id' => $accountId,
                'debit' => $debit,
                'credit' => $credit,
                'shop_id' => $line['shop_id'] ?? $defaultShopId,
                'party' => $line['party'] ?? null,
                'memo' => $line['memo'] ?? null,
            ];
        }

        if (count($normalised) < 2) {
            $fail('এন্ট্রিতে কমপক্ষে দুটি লাইন থাকতে হবে (An entry needs at least two lines)।');
        }

        $debits = round(array_sum(array_column($normalised, 'debit')), 2);
        $credits = round(array_sum(array_column($normalised, 'credit')), 2);

        if ($debits !== $credits) {
            $fail("ডেবিট ও ক্রেডিট সমান নয় (Debits {$debits} must equal credits {$credits})।");
        }

        $accounts = LedgerAccount::withoutGlobalScopes()->whereIn('id', array_column($normalised, 'account_id'))->get()->keyBy('id');

        foreach ($normalised as $line) {
            $account = $accounts->get($line['account_id']);

            if (! $account || (int) $account->company_id !== $companyId) {
                $fail('অ্যাকাউন্টটি এই কোম্পানির নয় (The account does not belong to this company)।');
            }

            if ($account->is_group || ! $account->is_active) {
                $fail("\"{$account->name}\" অ্যাকাউন্টে এন্ট্রি দেওয়া যায় না (Cannot post to a group or inactive account)।");
            }
        }

        return $normalised;
    }

    private function ensureOpenPeriod(int $companyId, Carbon $date): void
    {
        $closed = FiscalYear::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('is_closed', true)
            ->whereDate('starts_on', '<=', $date->toDateString())
            ->whereDate('ends_on', '>=', $date->toDateString())
            ->exists();

        if ($closed) {
            throw ValidationException::withMessages(['entry_date' => 'এই তারিখের অর্থবছর বন্ধ করা হয়েছে (The fiscal year for this date is closed)।']);
        }
    }

    private function nextNumber(int $companyId): string
    {
        $last = JournalEntry::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('number');

        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'JV-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
