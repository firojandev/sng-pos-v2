<?php

namespace Modules\Finance\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransaction;
use Modules\Finance\Models\CashSession;

/**
 * Daily cash drawer reconciliation on top of the cash accounts' ledger.
 *
 * Expected cash is worked out from the cash account's system balance: the
 * session's opening balance plus however much that balance moved while the
 * session was open. Edits and deletions of earlier sales or expenses move the
 * balance by their net change, so they land in the session they happened in.
 * Nothing here writes to the accounts or their transactions.
 */
class CashReconciliationService
{
    /**
     * Bangladeshi notes and coins offered on the cash count sheet.
     *
     * @var list<int>
     */
    public const DENOMINATIONS = [1000, 500, 200, 100, 50, 20, 10, 5, 2, 1];

    /**
     * Active cash accounts the current user can see.
     *
     * @return Collection<int, Account>
     */
    public function cashAccounts(): Collection
    {
        return Account::query()
            ->where('type', 'cash')
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    public function openSessionFor(Account $account): ?CashSession
    {
        return CashSession::query()->open()->where('account_id', $account->id)->latest('opened_at')->first();
    }

    public function lastClosedSessionFor(Account $account): ?CashSession
    {
        return CashSession::query()
            ->where('account_id', $account->id)
            ->where('status', CashSession::STATUS_CLOSED)
            ->latest('closed_at')
            ->latest('id')
            ->first();
    }

    /**
     * Cash that should be in the drawer when a new session opens: the last
     * physical closing count carried forward, plus anything the account
     * recorded while no session was open. With no earlier session, the
     * account's system balance.
     */
    public function expectedOpeningBalance(Account $account): float
    {
        $currentBalance = (float) $account->current_balance;
        $lastClosed = $this->lastClosedSessionFor($account);

        if (! $lastClosed) {
            return round($currentBalance, 2);
        }

        $movedSinceClose = $currentBalance - (float) $lastClosed->system_balance_at_close;

        return round((float) $lastClosed->counted_closing_balance + $movedSinceClose, 2);
    }

    /**
     * Cash that should be in the drawer right now for an open session.
     */
    public function expectedClosingBalance(CashSession $session, ?float $systemBalance = null): float
    {
        $systemBalance ??= (float) Account::withoutGlobalScopes()->whereKey($session->account_id)->value('current_balance');

        return round((float) $session->opening_balance + ($systemBalance - (float) $session->system_balance_at_open), 2);
    }

    /**
     * Cash movements recorded on the account during the session, grouped by
     * source, plus the net effect of edits or deletions of earlier entries.
     *
     * @return array{rows: list<array{source: string, type: string, bn: string, en: string, amount: float, count: int}>, cash_in: float, cash_out: float, other_changes: float, net_change: float}
     */
    public function movementSummary(CashSession $session, ?float $systemBalance = null, ?Carbon $until = null): array
    {
        $systemBalance ??= (float) Account::withoutGlobalScopes()->whereKey($session->account_id)->value('current_balance');
        $until ??= now();
        $labels = AccountTransaction::sourceLabels();

        $rows = AccountTransaction::withoutGlobalScopes()
            ->where('account_id', $session->account_id)
            ->where('created_at', '>=', $session->opened_at)
            ->where('created_at', '<=', $until)
            ->groupBy('source', 'type')
            ->selectRaw('source, type, SUM(amount) as amount, COUNT(*) as entries')
            ->orderBy('type')
            ->orderByDesc('amount')
            ->get()
            ->map(fn (AccountTransaction $row): array => [
                'source' => (string) $row->source,
                'type' => (string) $row->type,
                'bn' => $labels[$row->source]['bn'] ?? (string) $row->source,
                'en' => $labels[$row->source]['en'] ?? (string) $row->source,
                'amount' => round((float) $row->amount, 2),
                'count' => (int) $row->entries,
            ])
            ->values()
            ->all();

        $cashIn = round(array_sum(array_map(fn (array $row) => $row['type'] === 'in' ? $row['amount'] : 0, $rows)), 2);
        $cashOut = round(array_sum(array_map(fn (array $row) => $row['type'] === 'out' ? $row['amount'] : 0, $rows)), 2);
        $netChange = round($systemBalance - (float) $session->system_balance_at_open, 2);

        return [
            'rows' => $rows,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'other_changes' => round($netChange - ($cashIn - $cashOut), 2),
            'net_change' => $netChange,
        ];
    }

    /**
     * Open a session on a cash account. A null $openingCounts carries the
     * expected balance forward without a physical count.
     *
     * @param  array<int|string, int>|null  $openingCounts  denomination => quantity
     */
    public function open(Account $account, User $user, ?array $openingCounts, float $openingOtherAmount, ?string $note): CashSession
    {
        return DB::transaction(function () use ($account, $user, $openingCounts, $openingOtherAmount, $note) {
            $lockedAccount = Account::withoutGlobalScopes()->whereKey($account->id)->lockForUpdate()->firstOrFail();

            if ($this->openSessionFor($lockedAccount)) {
                throw ValidationException::withMessages([
                    'session' => 'এই ক্যাশ অ্যাকাউন্টে ইতিমধ্যে একটি সেশন চালু আছে। নতুন সেশনের আগে সেটি বন্ধ করুন। (A session is already open for this cash account. Close it first.)',
                ]);
            }

            $expectedOpening = $this->expectedOpeningBalance($lockedAccount);
            $countLines = $openingCounts === null ? null : $this->countLines($openingCounts, $openingOtherAmount);
            $countedOpening = $countLines === null ? null : round(array_sum(array_column($countLines, 'subtotal')), 2);
            $openingBalance = $countedOpening ?? $expectedOpening;

            $session = CashSession::create([
                'shop_id' => $lockedAccount->shop_id,
                'account_id' => $lockedAccount->id,
                'status' => CashSession::STATUS_OPEN,
                'opened_by' => $user->id,
                'opened_at' => now(),
                'system_balance_at_open' => (float) $lockedAccount->current_balance,
                'expected_opening_balance' => $expectedOpening,
                'counted_opening_balance' => $countedOpening,
                'opening_balance' => $openingBalance,
                'opening_difference' => round($openingBalance - $expectedOpening, 2),
                'opening_note' => $note,
            ]);

            if ($countLines !== null) {
                $session->counts()->createMany(array_map(fn (array $line) => $line + ['stage' => 'opening'], $countLines));
            }

            return $session;
        });
    }

    /**
     * Close a session with the physical closing count. A difference between the
     * counted and expected cash needs a reason.
     *
     * @param  array<int|string, int>  $closingCounts  denomination => quantity
     */
    public function close(CashSession $session, User $user, array $closingCounts, float $closingOtherAmount, ?string $note): CashSession
    {
        return DB::transaction(function () use ($session, $user, $closingCounts, $closingOtherAmount, $note) {
            $lockedAccount = Account::withoutGlobalScopes()->whereKey($session->account_id)->lockForUpdate()->firstOrFail();
            $lockedSession = CashSession::withoutGlobalScopes()->whereKey($session->id)->lockForUpdate()->firstOrFail();

            if (! $lockedSession->isOpen()) {
                throw ValidationException::withMessages([
                    'session' => 'এই সেশনটি ইতিমধ্যে বন্ধ করা হয়েছে। (This session is already closed.)',
                ]);
            }

            $systemBalance = (float) $lockedAccount->current_balance;
            $expectedClosing = $this->expectedClosingBalance($lockedSession, $systemBalance);
            $countLines = $this->countLines($closingCounts, $closingOtherAmount);
            $countedClosing = round(array_sum(array_column($countLines, 'subtotal')), 2);
            $difference = round($countedClosing - $expectedClosing, 2);

            if ($difference != 0.0 && trim((string) $note) === '') {
                throw ValidationException::withMessages([
                    'note' => 'গণনা করা ক্যাশ ও প্রত্যাশিত ক্যাশ মিলছে না — কারণ লিখুন। (Counted cash does not match expected cash — enter a reason.)',
                ]);
            }

            $closedAt = now();

            $lockedSession->update([
                'status' => CashSession::STATUS_CLOSED,
                'closed_by' => $user->id,
                'closed_at' => $closedAt,
                'system_balance_at_close' => $systemBalance,
                'expected_closing_balance' => $expectedClosing,
                'counted_closing_balance' => $countedClosing,
                'closing_difference' => $difference,
                'closing_note' => $note,
                'movement_summary' => $this->movementSummary($lockedSession, $systemBalance, $closedAt),
            ]);

            $lockedSession->counts()->createMany(array_map(fn (array $line) => $line + ['stage' => 'closing'], $countLines));

            return $lockedSession;
        });
    }

    /**
     * Count sheet lines for the known denominations with a quantity above zero,
     * plus loose change or other cash as a single line with denomination 0.
     *
     * @param  array<int|string, int|string|null>  $counts  denomination => quantity
     * @return list<array{denomination: int, quantity: int, subtotal: float}>
     */
    private function countLines(array $counts, float $otherAmount = 0.0): array
    {
        $lines = [];

        foreach (self::DENOMINATIONS as $denomination) {
            $quantity = (int) ($counts[$denomination] ?? $counts[(string) $denomination] ?? 0);

            if ($quantity > 0) {
                $lines[] = [
                    'denomination' => $denomination,
                    'quantity' => $quantity,
                    'subtotal' => round($denomination * $quantity, 2),
                ];
            }
        }

        $otherAmount = round($otherAmount, 2);
        if ($otherAmount > 0) {
            $lines[] = ['denomination' => 0, 'quantity' => 1, 'subtotal' => $otherAmount];
        }

        return $lines;
    }
}
