<?php

namespace Modules\Finance\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransaction;
use Modules\Finance\Models\AccountTransfer;

class AccountTransactionService
{
    /**
     * Get or create a default account for a given shop.
     */
    public function getDefaultAccount(int $shopId): Account
    {
        $account = Account::withoutGlobalScopes()
            ->where('shop_id', $shopId)
            ->where('status', 'active')
            ->where('is_default', true)
            ->first();

        if (! $account) {
            $account = Account::withoutGlobalScopes()
                ->where('shop_id', $shopId)
                ->where('status', 'active')
                ->first();
        }

        if (! $account) {
            $account = Account::withoutGlobalScopes()->create([
                'shop_id' => $shopId,
                'name' => 'নগদ টাকা (Cash)',
                'type' => 'cash',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_default' => false,
                'status' => 'active',
                'note' => 'প্রধান ক্যাশ অ্যাকাউন্ট (সিস্টেম নির্ধারিত)',
            ]);
        }

        return $account;
    }

    /**
     * Record a transaction and update the account's current balance atomically.
     */
    public function recordTransaction(
        Account $account,
        string $type,
        float $amount,
        string $source,
        ?Model $sourceable = null,
        ?string $note = null,
        \DateTimeInterface|string|null $occurredAt = null,
        ?int $userId = null
    ): ?AccountTransaction {
        if ($amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($account, $type, $amount, $source, $sourceable, $note, $occurredAt, $userId) {
            $lockedAccount = Account::withoutGlobalScopes()
                ->where('id', $account->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($type === 'in') {
                $lockedAccount->current_balance = (float) $lockedAccount->current_balance + $amount;
            } else {
                $lockedAccount->current_balance = (float) $lockedAccount->current_balance - $amount;
            }
            $lockedAccount->save();

            return AccountTransaction::withoutGlobalScopes()->create([
                'shop_id' => $lockedAccount->shop_id,
                'account_id' => $lockedAccount->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $lockedAccount->current_balance,
                'source' => $source,
                'sourceable_type' => $sourceable ? $sourceable->getMorphClass() : null,
                'sourceable_id' => $sourceable ? $sourceable->getKey() : null,
                'note' => $note,
                'occurred_at' => $occurredAt ?? now(),
                'created_by' => $userId ?? Auth::id(),
            ]);
        });
    }

    /**
     * Delete existing transactions attached to a sourceable and restore the account balance.
     */
    public function deleteTransactionsFor(Model $sourceable): void
    {
        DB::transaction(function () use ($sourceable) {
            $transactions = AccountTransaction::withoutGlobalScopes()
                ->where('sourceable_type', $sourceable->getMorphClass())
                ->where('sourceable_id', $sourceable->getKey())
                ->get();

            foreach ($transactions as $tx) {
                $lockedAccount = Account::withoutGlobalScopes()
                    ->where('id', $tx->account_id)
                    ->lockForUpdate()
                    ->first();

                if ($lockedAccount) {
                    if ($tx->type === 'in') {
                        $lockedAccount->current_balance = (float) $lockedAccount->current_balance - (float) $tx->amount;
                    } else {
                        $lockedAccount->current_balance = (float) $lockedAccount->current_balance + (float) $tx->amount;
                    }
                    $lockedAccount->save();
                }

                $tx->forceDelete();
            }
        });
    }

    /**
     * Execute a fund transfer between two accounts.
     */
    public function transfer(
        Account $fromAccount,
        Account $toAccount,
        float $amount,
        float $charge = 0,
        ?string $transferDate = null,
        ?string $note = null,
        ?int $userId = null
    ): AccountTransfer {
        return DB::transaction(function () use ($fromAccount, $toAccount, $amount, $charge, $transferDate, $note, $userId) {
            $transferDate = $transferDate ?? now()->toDateString();
            $transferNo = 'TRF-'.now()->format('ymd').'-'.str_pad((string) random_int(100, 999), 3, '0', STR_PAD_LEFT);

            $transfer = AccountTransfer::withoutGlobalScopes()->create([
                'shop_id' => $fromAccount->shop_id,
                'transfer_no' => $transferNo,
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => $amount,
                'charge' => $charge,
                'transfer_date' => $transferDate,
                'note' => $note,
                'created_by' => $userId ?? Auth::id(),
            ]);

            // 1. Debit from source account (amount + charge)
            $totalDebit = $amount + $charge;
            $fromNote = $charge > 0
                ? "ট্রান্সফার: {$toAccount->name} তে (ফি: ৳{$charge})"
                : "ট্রান্সফার: {$toAccount->name} তে";

            $this->recordTransaction(
                account: $fromAccount,
                type: 'out',
                amount: $totalDebit,
                source: 'transfer_out',
                sourceable: $transfer,
                note: $note ? "{$fromNote} - {$note}" : $fromNote,
                occurredAt: $transferDate.' '.now()->format('H:i:s'),
                userId: $userId
            );

            // 2. Credit to target account (amount)
            $toNote = "ট্রান্সফার গ্রহণ: {$fromAccount->name} থেকে";
            $this->recordTransaction(
                account: $toAccount,
                type: 'in',
                amount: $amount,
                source: 'transfer_in',
                sourceable: $transfer,
                note: $note ? "{$toNote} - {$note}" : $toNote,
                occurredAt: $transferDate.' '.now()->format('H:i:s'),
                userId: $userId
            );

            return $transfer;
        });
    }

    /**
     * Set an account as the default account for its shop.
     */
    public function setDefaultAccount(Account $account): void
    {
        if ($account->isCash()) {
            throw new \InvalidArgumentException('Cash account cannot be set as default.');
        }

        DB::transaction(function () use ($account) {
            Account::withoutGlobalScopes()
                ->where('shop_id', $account->shop_id)
                ->where('id', '!=', $account->id)
                ->update(['is_default' => false]);

            $account->update(['is_default' => true]);
        });
    }
}
