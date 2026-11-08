<?php

namespace Modules\Accounting\Services;

use Illuminate\Support\Facades\DB;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Account;
use Modules\Product\Models\Batch;
use Modules\Purchase\Models\Purchase;
use Modules\Sales\Models\Sale;
use Modules\Supplier\Models\Supplier;

/**
 * Compares the ledger with the app's own figures (money account balances,
 * customer and supplier dues, stock at batch cost), so differences can be
 * spotted and corrected.
 */
class Reconciliation
{
    public function __construct(private LedgerService $ledger, private ChartOfAccounts $chart) {}

    /**
     * @return list<array{label: string, app: float, ledger: float, difference: float}>
     */
    public function compare(int $companyId): array
    {
        $shopIds = DB::table('shops')->where('company_id', $companyId)->pluck('id');
        $totals = $this->ledger->totalsByAccount($companyId);
        $ledgerBalance = function ($account) use ($totals): float {
            $row = $totals[$account->id] ?? null;

            return $row ? $account->normalBalance((float) $row->debit, (float) $row->credit) : 0.0;
        };

        $rows = [];

        foreach (Account::withoutGlobalScopes()->whereIn('shop_id', $shopIds)->whereIn('type', ['cash', 'bank', 'mfs'])->orderBy('id')->get() as $moneyAccount) {
            $ledgerAccount = $this->chart->forMoneyAccount($moneyAccount);
            $rows[] = $this->row($ledgerAccount->name, (float) $moneyAccount->current_balance, $ledgerBalance($ledgerAccount));
        }

        $receivable = (float) Customer::withoutGlobalScopes()->where('company_id', $companyId)->sum('opening_due')
            + (float) Sale::withoutGlobalScopes()->whereIn('shop_id', $shopIds)->whereNull('deleted_at')->whereNotNull('customer_id')->sum('due_amount');
        $rows[] = $this->row('Accounts Receivable', $receivable, $ledgerBalance($this->chart->account($companyId, 'accounts_receivable')));

        $payable = (float) Supplier::withoutGlobalScopes()->where('company_id', $companyId)->sum('opening_due')
            + (float) Purchase::withoutGlobalScopes()->whereIn('shop_id', $shopIds)->whereNull('deleted_at')->whereNotNull('supplier_id')->sum('due_amount');
        $rows[] = $this->row('Accounts Payable', $payable, $ledgerBalance($this->chart->account($companyId, 'accounts_payable')));

        $inventory = (float) Batch::withoutGlobalScopes()->whereIn('batches.shop_id', $shopIds)
            ->join('products', 'products.id', '=', 'batches.product_id')
            ->sum(DB::raw('batches.quantity * COALESCE(batches.unit_cost, products.purchase_price)'));
        $rows[] = $this->row('Inventory', $inventory, $ledgerBalance($this->chart->account($companyId, 'inventory')));

        return $rows;
    }

    /**
     * @return array{label: string, app: float, ledger: float, difference: float}
     */
    private function row(string $label, float $app, float $ledger): array
    {
        return ['label' => $label, 'app' => round($app, 2), 'ledger' => round($ledger, 2), 'difference' => round($ledger - $app, 2)];
    }
}
