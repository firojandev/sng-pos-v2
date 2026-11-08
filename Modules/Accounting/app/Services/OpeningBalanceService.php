<?php

namespace Modules\Accounting\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\LedgerAccount;
use Modules\Company\Models\Company;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Account;
use Modules\FinanceManagement\Models\Asset;
use Modules\FinanceManagement\Models\Debt;
use Modules\FinanceManagement\Models\Lend;
use Modules\FinanceManagement\Models\SecurityMoney;
use Modules\Product\Models\Batch;
use Modules\Purchase\Models\Purchase;
use Modules\Sales\Models\Sale;
use Modules\Shop\Models\Shop;
use Modules\Supplier\Models\Supplier;

/**
 * Starts a company's ledger from its current position: one balanced entry,
 * built per shop from the same figures as the balance sheet (money
 * accounts, receivables, stock at batch cost, loans, deposits, fixed assets,
 * payables). The difference is the owners' opening equity.
 */
class OpeningBalanceService
{
    public const REFERENCE = 'OPENING-BALANCE';

    public function __construct(private ChartOfAccounts $chart, private LedgerService $ledger) {}

    public function isPosted(int $companyId): bool
    {
        return JournalEntry::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('reference', self::REFERENCE)
            ->whereNull('reversed_at')
            ->exists();
    }

    /**
     * The lines the opening entry would post, balanced with opening equity.
     *
     * @return list<array{account: LedgerAccount, debit: float, credit: float, shop_id: int, party: ?Model, memo: string}>
     */
    public function lines(Company $company): array
    {
        $this->chart->ensureFor($company->id);
        $lines = [];

        foreach (Shop::where('company_id', $company->id)->orderBy('id')->get() as $shop) {
            $shopLines = $this->shopLines($company->id, $shop);
            $difference = round(array_sum(array_column($shopLines, 'debit')) - array_sum(array_column($shopLines, 'credit')), 2);

            if ($difference != 0.0) {
                $shopLines[] = $this->line($company->id, 'opening_balance_equity', $shop,
                    $difference < 0 ? -$difference : 0, $difference > 0 ? $difference : 0, 'Opening equity');
            }

            array_push($lines, ...$shopLines);
        }

        return $lines;
    }

    public function post(Company $company, Carbon|string $date): JournalEntry
    {
        if ($this->isPosted($company->id)) {
            throw ValidationException::withMessages(['opening' => 'প্রারম্ভিক ব্যালেন্স ইতিমধ্যে পোস্ট করা হয়েছে (The opening balance is already posted)।']);
        }

        $lines = $this->lines($company);

        if ($lines === []) {
            throw ValidationException::withMessages(['opening' => 'পোস্ট করার মতো কোনো ব্যালেন্স নেই (There are no balances to post)।']);
        }

        $entry = $this->ledger->post($company->id, $date, $lines, 'Opening balance', null, null, self::REFERENCE);

        // Automatic posting starts from this date.
        app(AutoPosting::class)->forgetGoLiveDates();

        return $entry;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function shopLines(int $companyId, Shop $shop): array
    {
        $lines = [];
        $add = function (string $key, float $amount, bool $isDebit, string $memo, $party = null) use (&$lines, $companyId, $shop) {
            $amount = round($amount, 2);
            if ($amount > 0) {
                $lines[] = $this->line($companyId, $key, $shop, $isDebit ? $amount : 0, $isDebit ? 0 : $amount, $memo, $party);
            }
        };

        // Cash, bank and mobile-banking balances, each in its own ledger account.
        foreach (Account::withoutGlobalScopes()->where('shop_id', $shop->id)->whereIn('type', ['cash', 'bank', 'mfs'])->where('status', 'active')->get() as $moneyAccount) {
            $balance = round((float) $moneyAccount->current_balance, 2);
            if ($balance != 0.0) {
                $lines[] = [
                    'account' => $this->chart->forMoneyAccount($moneyAccount),
                    'debit' => max($balance, 0), 'credit' => max(-$balance, 0),
                    'shop_id' => $shop->id, 'party' => null, 'memo' => $moneyAccount->name,
                ];
            }
        }

        // Receivables per customer: opening dues of customers created here and this shop's unpaid sales.
        $customerDues = Sale::withoutGlobalScopes()->where('shop_id', $shop->id)->whereNull('deleted_at')
            ->whereNotNull('customer_id')->where('due_amount', '>', 0)
            ->groupBy('customer_id')->selectRaw('customer_id, SUM(due_amount) as due')->pluck('due', 'customer_id');
        foreach (Customer::withoutGlobalScopes()->where('shop_id', $shop->id)->where('opening_due', '>', 0)->get(['id', 'opening_due']) as $customer) {
            $customerDues[$customer->id] = (float) ($customerDues[$customer->id] ?? 0) + (float) $customer->opening_due;
        }
        foreach ($customerDues as $customerId => $due) {
            $add('accounts_receivable', (float) $due, true, 'Customer due', Customer::withoutGlobalScopes()->find($customerId));
        }

        // Payables per supplier.
        $supplierDues = Purchase::withoutGlobalScopes()->where('shop_id', $shop->id)->whereNull('deleted_at')
            ->whereNotNull('supplier_id')->where('due_amount', '>', 0)
            ->groupBy('supplier_id')->selectRaw('supplier_id, SUM(due_amount) as due')->pluck('due', 'supplier_id');
        foreach (Supplier::withoutGlobalScopes()->where('shop_id', $shop->id)->where('opening_due', '>', 0)->get(['id', 'opening_due']) as $supplier) {
            $supplierDues[$supplier->id] = (float) ($supplierDues[$supplier->id] ?? 0) + (float) $supplier->opening_due;
        }
        foreach ($supplierDues as $supplierId => $due) {
            $add('accounts_payable', (float) $due, false, 'Supplier due', Supplier::withoutGlobalScopes()->find($supplierId));
        }

        $add('inventory', (float) Batch::withoutGlobalScopes()->where('batches.shop_id', $shop->id)
            ->join('products', 'products.id', '=', 'batches.product_id')
            ->sum(DB::raw('batches.quantity * COALESCE(batches.unit_cost, products.purchase_price)')), true, 'Stock at batch cost');

        $add('loans_receivable', (float) Lend::withoutGlobalScopes()->where('shop_id', $shop->id)->whereNull('deleted_at')->where('status', 'due')->sum('amount'), true, 'Loans given');
        $add('security_deposits_paid', (float) SecurityMoney::withoutGlobalScopes()->where('shop_id', $shop->id)->whereNull('deleted_at')->where('status', 'paid')->sum('amount'), true, 'Security deposits paid');
        $add('loans_payable', (float) Debt::withoutGlobalScopes()->where('shop_id', $shop->id)->whereNull('deleted_at')->where('status', 'unpaid')->sum('amount'), false, 'Loans taken');
        $add('security_deposits_received', (float) SecurityMoney::withoutGlobalScopes()->where('shop_id', $shop->id)->whereNull('deleted_at')->where('status', 'received')->sum('amount'), false, 'Security deposits received');

        // Fixed assets at cost, with the depreciation recorded so far.
        $assets = Asset::withoutGlobalScopes()->where('shop_id', $shop->id)->whereNull('deleted_at')->get(['amount', 'depreciation', 'depreciation_type']);
        $add('fixed_assets', (float) $assets->sum('amount'), true, 'Fixed assets at cost');
        $add('accumulated_depreciation', (float) $assets->sum(fn ($asset) => min(
            (float) $asset->amount,
            $asset->depreciation_type === 'percentage' ? (float) $asset->amount * (float) $asset->depreciation / 100 : (float) $asset->depreciation,
        )), false, 'Depreciation to date');

        return $lines;
    }

    /**
     * @return array<string, mixed>
     */
    private function line(int $companyId, string $key, Shop $shop, float $debit, float $credit, string $memo, $party = null): array
    {
        return [
            'account' => $this->chart->account($companyId, $key),
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
            'shop_id' => $shop->id,
            'party' => $party,
            'memo' => $memo,
        ];
    }
}
