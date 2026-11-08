<?php

namespace Modules\Cashbox\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Cashbox\Models\CashTransaction;

/**
 * Keeps the cashbox rows of a sale or purchase in step with what was paid.
 *
 * Customers and suppliers are shared by the company, so a due can be settled
 * from another shop's account. Each shop gets a cashbox row for the payments
 * made through its own accounts; the document's shop gets the remainder, so
 * a single-shop document keeps exactly one row for its full paid amount.
 */
trait SyncsCashByShop
{
    /**
     * @param  string  $paymentsTable  e.g. sale_payments
     * @param  string  $foreignKey  the payments table's key to the document, e.g. sale_id
     */
    protected function syncCashByShop(Model $document, string $paymentsTable, string $foreignKey, string $type, string $source, string $occurredAt): void
    {
        $paidAmount = round((float) $document->paid_amount, 2);

        if ($paidAmount <= 0) {
            $this->removeCash($document);

            return;
        }

        $paidThroughOtherShops = DB::table($paymentsTable)
            ->join('accounts', 'accounts.id', '=', "{$paymentsTable}.account_id")
            ->where("{$paymentsTable}.{$foreignKey}", $document->getKey())
            ->whereNull("{$paymentsTable}.deleted_at")
            ->where('accounts.shop_id', '!=', $document->shop_id)
            ->groupBy('accounts.shop_id')
            ->selectRaw("accounts.shop_id, SUM({$paymentsTable}.amount) as amount")
            ->pluck('amount', 'shop_id')
            ->map(fn ($amount) => round((float) $amount, 2));

        $amountsByShop = $paidThroughOtherShops->put(
            $document->shop_id,
            round($paidAmount - $paidThroughOtherShops->sum(), 2),
        )->filter(fn (float $amount) => $amount > 0);

        foreach ($amountsByShop as $shopId => $amount) {
            $row = $this->cashRows($document)->withTrashed()->firstOrNew([
                'sourceable_type' => $document::class,
                'sourceable_id' => $document->getKey(),
                'shop_id' => $shopId,
            ]);
            $row->fill([
                'type' => $type,
                'source' => $source,
                'amount' => $amount,
                'note' => $document->invoice_no,
                'occurred_at' => $occurredAt,
                'created_by' => Auth::id(),
            ]);
            $row->deleted_at = null;
            $row->save();
        }

        $this->cashRows($document)->whereNotIn('shop_id', $amountsByShop->keys())->delete();
    }

    protected function removeCash(Model $document): void
    {
        $this->cashRows($document)->delete();
    }

    /**
     * All cashbox rows of the document, whichever shop they belong to.
     */
    private function cashRows(Model $document)
    {
        return CashTransaction::withoutGlobalScope('shop')
            ->where('sourceable_type', $document::class)
            ->where('sourceable_id', $document->getKey());
    }
}
