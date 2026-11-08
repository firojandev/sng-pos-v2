<?php

namespace Modules\Accounting\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\JournalEntry;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\LoyaltyPointTransaction;
use Modules\Customer\Models\LoyaltyProgram;
use Modules\Employee\Models\Employee;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransaction;
use Modules\Finance\Models\AccountTransfer;
use Modules\FinanceManagement\Models\Asset;
use Modules\FinanceManagement\Models\AssetDepreciation;
use Modules\Payroll\Models\FinalSettlement;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Models\StatutoryPayment;
use Modules\Product\Models\Batch;
use Modules\Product\Models\StockAdjustment;
use Modules\Product\Models\StockTransfer;
use Modules\Purchase\Models\Purchase;
use Modules\Purchase\Models\PurchasePayment;
use Modules\Purchase\Models\PurchaseReturn;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SalePayment;
use Modules\Sales\Models\SaleReturn;
use Modules\Supplier\Models\Supplier;
use Throwable;

/**
 * Keeps the ledger in step with the business: every sale, return, purchase,
 * stock movement, money movement and loyalty change has one journal entry.
 *
 * sync() works out the entry a record should have now. If that differs
 * from the entry already posted, the old one is reversed and the new one
 * posted; a deleted record just has its entry reversed. Posting starts on
 * the company's go-live date (its opening balance); earlier records are
 * already in the opening balance.
 */
class AutoPosting
{
    /**
     * Where the other side of a money movement goes, by its source.
     *
     * @var array<string, string>
     */
    private const MONEY_COUNTER_ACCOUNTS = [
        'sale' => 'accounts_receivable',
        'sale_return' => 'accounts_receivable',
        'purchase' => 'accounts_payable',
        'purchase_return' => 'accounts_payable',
        'expense' => 'general_expenses',
        'income' => 'other_income',
        'opening_balance' => 'opening_balance_equity',
        'cash_in' => 'owners_capital',
        'cash_out' => 'owners_capital',
        'manual_adjustment' => 'owners_capital',
        'debt_received' => 'loans_payable',
        'debt_repaid' => 'loans_payable',
        'lend_given' => 'loans_receivable',
        'lend_repaid' => 'loans_receivable',
        'security_money_paid' => 'security_deposits_paid',
        'security_money_received' => 'security_deposits_received',
        'salary' => 'salary_payable',
        'employee_advance' => 'employee_advances',
        'employee_advance_repaid' => 'employee_advances',
    ];

    /**
     * @var array<int, ?string>
     */
    private array $goLiveDates = [];

    public function __construct(private LedgerService $ledger, private ChartOfAccounts $chart) {}

    public function sync(Model $record): void
    {
        try {
            DB::transaction(fn () => $this->syncRecord($record));
        } catch (Throwable $exception) {
            // The business record is already saved; a posting problem (e.g. a
            // closed year) is reported rather than undoing the sale or purchase.
            report($exception);
        }
    }

    /**
     * The day automatic posting starts for a company (its opening balance).
     */
    public function goLiveDate(int $companyId): ?string
    {
        if (! array_key_exists($companyId, $this->goLiveDates)) {
            $date = JournalEntry::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('reference', OpeningBalanceService::REFERENCE)
                ->whereNull('reversed_at')
                ->value('entry_date');

            $this->goLiveDates[$companyId] = $date ? Carbon::parse($date)->toDateString() : null;
        }

        return $this->goLiveDates[$companyId];
    }

    public function forgetGoLiveDates(): void
    {
        $this->goLiveDates = [];
    }

    private function syncRecord(Model $record): void
    {
        $current = JournalEntry::withoutGlobalScopes()
            ->where('source_type', $record::class)
            ->where('source_id', $record->getKey())
            ->whereNull('reversed_at')
            ->whereNull('reversal_of_id')
            ->with('lines')
            ->first();

        $wanted = $this->entryFor($record);

        if ($current && $wanted && $this->signature($current) === $this->signature($wanted)) {
            return;
        }

        if ($current) {
            $this->ledger->reverse($current, $wanted['date'] ?? now(), 'Correction of '.$current->number);
        }

        if ($wanted) {
            $this->ledger->post(
                $wanted['company_id'],
                $wanted['date'],
                $wanted['lines'],
                $wanted['narration'],
                $wanted['shop_id'],
                $record,
                $wanted['reference'],
            );
        }
    }

    /**
     * The entry a record should have, or null when it should have none.
     *
     * @return array{company_id: int, date: string, shop_id: ?int, narration: string, reference: ?string, lines: list<array<string, mixed>>}|null
     */
    private function entryFor(Model $record): ?array
    {
        if (! $record->exists || (method_exists($record, 'trashed') && $record->trashed())) {
            return null;
        }

        $entry = match (true) {
            $record instanceof Sale => $this->forSale($record),
            $record instanceof SaleReturn => $this->forSaleReturn($record),
            $record instanceof Purchase => $this->forPurchase($record),
            $record instanceof PurchaseReturn => $this->forPurchaseReturn($record),
            $record instanceof AccountTransaction => $this->forMoneyMovement($record),
            $record instanceof AccountTransfer => $this->forAccountTransfer($record),
            $record instanceof StockAdjustment => $this->forStockAdjustment($record),
            $record instanceof StockTransfer => $this->forStockTransfer($record),
            $record instanceof LoyaltyPointTransaction => $this->forLoyalty($record),
            $record instanceof PayrollRun => $this->forPayrollRun($record),
            $record instanceof FinalSettlement => $this->forFinalSettlement($record),
            $record instanceof StatutoryPayment => $this->forStatutoryPayment($record),
            $record instanceof AssetDepreciation => $this->forDepreciation($record),
            default => null,
        };

        if (! $entry) {
            return null;
        }

        $goLive = $this->goLiveDate($entry['company_id']);

        if (! $goLive || $entry['date'] < $goLive) {
            return null;
        }

        $entry['lines'] = array_values(array_filter($entry['lines'], fn (array $line) => round((float) ($line['debit'] ?? 0), 2) > 0 || round((float) ($line['credit'] ?? 0), 2) > 0));

        return count($entry['lines']) >= 2 ? $entry : null;
    }

    private function forSale(Sale $sale): ?array
    {
        $companyId = $this->companyOfShop($sale->shop_id);
        $customer = $sale->customer_id ? Customer::withoutGlobalScopes()->find($sale->customer_id) : null;
        $adjustment = (float) $sale->adjustment;
        $cost = (float) $sale->items()->sum('cost_total');

        return $this->entry($companyId, $sale->sale_date, $sale->shop_id, 'Sale '.$sale->invoice_no, $sale->invoice_no, [
            $this->debit($companyId, 'accounts_receivable', (float) $sale->total, $customer),
            $this->debit($companyId, 'discounts_allowed', (float) $sale->discount + max(-$adjustment, 0)),
            $this->debit($companyId, 'loyalty_liability', (float) $sale->loyalty_discount, $customer),
            $this->credit($companyId, 'sales', (float) $sale->subtotal),
            $this->credit($companyId, 'vat_payable', (float) $sale->tax),
            $this->credit($companyId, 'delivery_income', (float) $sale->delivery_charge),
            $this->credit($companyId, 'other_income', max($adjustment, 0)),
            $this->debit($companyId, 'cogs', $cost),
            $this->credit($companyId, 'inventory', $cost),
        ]);
    }

    private function forSaleReturn(SaleReturn $saleReturn): ?array
    {
        $sale = Sale::withoutGlobalScopes()->withTrashed()->find($saleReturn->sale_id);
        if (! $sale) {
            return null;
        }

        $companyId = $this->companyOfShop($sale->shop_id);
        $customer = $sale->customer_id ? Customer::withoutGlobalScopes()->find($sale->customer_id) : null;

        // The returned quantity's share of each sold line's recorded cost.
        $cost = (float) $saleReturn->items()->get()->sum(function ($returned) {
            $sold = DB::table('sale_items')->where('id', $returned->sale_item_id)->first(['quantity', 'cost_total']);

            return $sold && (float) $sold->quantity > 0 ? (float) $sold->cost_total * (float) $returned->quantity / (float) $sold->quantity : 0;
        });

        return $this->entry($companyId, $saleReturn->return_date, $sale->shop_id, 'Sale return '.$saleReturn->return_no, $saleReturn->return_no, [
            $this->debit($companyId, 'sales_returns', (float) $saleReturn->subtotal),
            $this->credit($companyId, 'accounts_receivable', (float) $saleReturn->subtotal, $customer),
            $this->debit($companyId, 'inventory', $cost),
            $this->credit($companyId, 'cogs', $cost),
        ]);
    }

    private function forPurchase(Purchase $purchase): ?array
    {
        $companyId = $this->companyOfShop($purchase->shop_id);
        $supplier = $purchase->supplier_id ? Supplier::withoutGlobalScopes()->find($purchase->supplier_id) : null;

        return $this->entry($companyId, $purchase->purchase_date, $purchase->shop_id, 'Purchase '.$purchase->invoice_no, $purchase->invoice_no, [
            $this->debit($companyId, 'inventory', (float) $purchase->total),
            $this->credit($companyId, 'accounts_payable', (float) $purchase->total, $supplier),
        ]);
    }

    private function forPurchaseReturn(PurchaseReturn $purchaseReturn): ?array
    {
        $purchase = Purchase::withoutGlobalScopes()->withTrashed()->find($purchaseReturn->purchase_id);
        if (! $purchase) {
            return null;
        }

        $companyId = $this->companyOfShop($purchase->shop_id);
        $supplier = $purchase->supplier_id ? Supplier::withoutGlobalScopes()->find($purchase->supplier_id) : null;

        return $this->entry($companyId, $purchaseReturn->return_date, $purchase->shop_id, 'Purchase return '.$purchaseReturn->return_no, $purchaseReturn->return_no, [
            $this->debit($companyId, 'accounts_payable', (float) $purchaseReturn->subtotal, $supplier),
            $this->credit($companyId, 'inventory', (float) $purchaseReturn->subtotal),
        ]);
    }

    /**
     * Cash, bank or mobile-banking money in or out. Transfers between money
     * accounts are posted from the transfer itself.
     */
    private function forMoneyMovement(AccountTransaction $transaction): ?array
    {
        $counterKey = self::MONEY_COUNTER_ACCOUNTS[$transaction->source] ?? null;
        $moneyAccount = Account::withoutGlobalScopes()->withTrashed()->find($transaction->account_id);

        if (! $counterKey || ! $moneyAccount) {
            return null;
        }

        $companyId = $this->companyOfShop($moneyAccount->shop_id);
        [$party, $counterShopId] = $this->partyAndShopOf($transaction, $moneyAccount->shop_id);
        $amount = (float) $transaction->amount;
        $isIn = $transaction->type === 'in';

        $moneyLine = ['account' => $this->chart->forMoneyAccount($moneyAccount), 'debit' => $isIn ? $amount : 0, 'credit' => $isIn ? 0 : $amount, 'shop_id' => $moneyAccount->shop_id];
        $counterLine = ['account' => $this->chart->account($companyId, $counterKey), 'debit' => $isIn ? 0 : $amount, 'credit' => $isIn ? $amount : 0, 'shop_id' => $counterShopId, 'party' => $party];

        return $this->entry($companyId, $transaction->occurred_at, $moneyAccount->shop_id, $transaction->note ?: ucfirst(str_replace('_', ' ', $transaction->source)), null, [$moneyLine, $counterLine]);
    }

    private function forAccountTransfer(AccountTransfer $transfer): ?array
    {
        $from = Account::withoutGlobalScopes()->withTrashed()->find($transfer->from_account_id);
        $to = Account::withoutGlobalScopes()->withTrashed()->find($transfer->to_account_id);

        if (! $from || ! $to) {
            return null;
        }

        $companyId = $this->companyOfShop($from->shop_id);
        $amount = (float) $transfer->amount;
        $charge = (float) $transfer->charge;

        return $this->entry($companyId, $transfer->transfer_date, $from->shop_id, 'Transfer '.$transfer->transfer_no, $transfer->transfer_no, [
            ['account' => $this->chart->forMoneyAccount($to), 'debit' => $amount, 'shop_id' => $to->shop_id],
            $this->debit($companyId, 'general_expenses', $charge),
            ['account' => $this->chart->forMoneyAccount($from), 'credit' => $amount + $charge, 'shop_id' => $from->shop_id],
        ]);
    }

    private function forStockAdjustment(StockAdjustment $adjustment): ?array
    {
        $batch = Batch::withoutGlobalScopes()->find($adjustment->batch_id);
        $companyId = $this->companyOfShop($adjustment->shop_id);
        $value = (float) $adjustment->quantity * (float) ($batch?->unit_cost ?? 0);
        $isIncrease = $adjustment->type === 'increase';

        return $this->entry($companyId, $adjustment->created_at, $adjustment->shop_id, 'Stock adjustment', null, [
            $isIncrease ? $this->debit($companyId, 'inventory', $value) : $this->debit($companyId, 'stock_adjustment_loss', $value),
            $isIncrease ? $this->credit($companyId, 'stock_adjustment_loss', $value) : $this->credit($companyId, 'inventory', $value),
        ]);
    }

    /**
     * Stock received by another shop moves between the two shops' inventory.
     */
    private function forStockTransfer(StockTransfer $transfer): ?array
    {
        if ($transfer->status !== 'received' || ! $transfer->isBetweenShops()) {
            return null;
        }

        $companyId = $this->companyOfShop($transfer->shop_id);
        $value = (float) $transfer->items()->get()->sum(fn ($item) => (float) $item->quantity * (float) $item->unit_cost);

        return $this->entry($companyId, $transfer->received_at ?? $transfer->updated_at, $transfer->to_shop_id, 'Stock transfer '.$transfer->transfer_no, $transfer->transfer_no, [
            ['account' => $this->chart->account($companyId, 'inventory'), 'debit' => $value, 'shop_id' => $transfer->to_shop_id],
            ['account' => $this->chart->account($companyId, 'inventory'), 'credit' => $value, 'shop_id' => $transfer->shop_id],
        ]);
    }

    /**
     * Points earned (or taken back, or expired) change what the company owes
     * its members. Redemptions are posted with the sale they paid for.
     */
    private function forLoyalty(LoyaltyPointTransaction $points): ?array
    {
        $isFromSale = $points->source_type === Sale::class;

        if (in_array($points->type, [LoyaltyPointTransaction::REDEEM], true)
            || ($points->type === LoyaltyPointTransaction::ADJUST && $isFromSale)) {
            return null;
        }

        $companyId = (int) $points->company_id;
        $pointValue = (float) (LoyaltyProgram::forCompany($companyId)->point_value ?? 0);
        $value = round(abs((int) $points->points) * $pointValue, 2);
        $customer = Customer::withoutGlobalScopes()->find($points->customer_id);
        $grows = (int) $points->points > 0;

        return $this->entry($companyId, $points->created_at, $points->shop_id, 'Loyalty points '.$points->type, null, [
            $grows ? $this->debit($companyId, 'loyalty_expense', $value) : $this->debit($companyId, 'loyalty_liability', $value, $customer),
            $grows ? $this->credit($companyId, 'loyalty_liability', $value, $customer) : $this->credit($companyId, 'loyalty_expense', $value),
        ]);
    }

    /**
     * An approved payroll: the pay earned is an expense (absence deductions
     * reduce it), provident fund and tax go to their payables, installments
     * reduce the employee's advance, and the net pay is owed to each
     * employee until it is paid.
     */
    private function forPayrollRun(PayrollRun $run): ?array
    {
        if ($run->status !== 'approved') {
            return null;
        }

        $companyId = $this->companyOfShop($run->shop_id);
        $expense = $pfEmployee = $pfEmployer = $tax = $otherIncome = 0.0;
        $employeeLines = [];

        foreach ($run->payslips()->with('items')->get() as $payslip) {
            $employee = Employee::withoutGlobalScopes()->find($payslip->employee_id);

            foreach ($payslip->items as $item) {
                $amount = (float) $item->amount;

                match (true) {
                    $item->type === 'earning' => $expense += $amount,
                    in_array($item->code, ['ABSENT', 'LATE'], true) => $expense -= $amount,
                    $item->code === 'PF' => $pfEmployee += $amount,
                    $item->code === 'TAX' => $tax += $amount,
                    $item->code === 'LOAN' => $employeeLines[] = $this->credit($companyId, 'employee_advances', $amount, $employee),
                    default => $otherIncome += $amount,
                };
            }

            $pfEmployer += (float) $payslip->pf_employer;
            $employeeLines[] = $this->credit($companyId, 'salary_payable', (float) $payslip->net_pay, $employee);
        }

        $date = $run->type === 'bonus' && $run->pay_date ? $run->pay_date : $run->month->copy()->endOfMonth();
        $narration = ($run->type === 'bonus' ? 'Festival bonus ' : 'Payroll ').$run->month->format('M Y');

        return $this->entry($companyId, $date, $run->shop_id, $narration, 'PAY-'.$run->id, [
            $this->debit($companyId, 'salary_expense', $expense),
            $this->debit($companyId, 'pf_expense', $pfEmployer),
            $this->credit($companyId, 'pf_payable', $pfEmployee),
            $this->credit($companyId, 'pf_employer_payable', $pfEmployer),
            $this->credit($companyId, 'tds_payable', $tax),
            $this->credit($companyId, 'other_income', $otherIncome),
            ...$employeeLines,
        ]);
    }

    /**
     * A finalized settlement: benefits are an expense, the provident fund
     * paid out (or forfeited) leaves the PF payable, loans still owed are
     * recovered and the net is owed to the employee until paid.
     */
    private function forFinalSettlement(FinalSettlement $settlement): ?array
    {
        if ($settlement->status !== 'finalized') {
            return null;
        }

        $companyId = $this->companyOfShop($settlement->shop_id);
        $employee = Employee::withoutGlobalScopes()->find($settlement->employee_id);
        $expense = $pfOwn = $pfEmployer = $otherIncome = 0.0;
        $loanLines = [];

        foreach ($settlement->items()->get() as $item) {
            $amount = (float) $item->amount;

            match (true) {
                $item->code === 'PF_OWN' => $pfOwn += $amount,
                $item->code === 'PF_EMPLOYER' => $pfEmployer += $amount,
                $item->type === 'earning' => $expense += $amount,
                $item->code === 'LOAN' => $loanLines[] = $this->credit($companyId, 'employee_advances', $amount, $employee),
                default => $otherIncome += $amount,
            };
        }

        $forfeited = (float) $settlement->pf_forfeited;

        return $this->entry($companyId, $settlement->separation_date, $settlement->shop_id, 'Final settlement '.($employee?->name ?? ''), 'FS-'.$settlement->id, [
            $this->debit($companyId, 'salary_expense', $expense),
            $this->debit($companyId, 'pf_payable', $pfOwn),
            $this->debit($companyId, 'pf_employer_payable', $pfEmployer + $forfeited),
            $this->credit($companyId, 'pf_expense', $forfeited),
            $this->credit($companyId, 'other_income', $otherIncome),
            ...$loanLines,
            $this->credit($companyId, 'salary_payable', (float) $settlement->net_pay, $employee),
        ]);
    }

    /**
     * A month's depreciation of a fixed asset.
     */
    private function forDepreciation(AssetDepreciation $depreciation): ?array
    {
        $companyId = $this->companyOfShop($depreciation->shop_id);
        $assetName = Asset::withoutGlobalScopes()->withTrashed()->whereKey($depreciation->asset_id)->value('name');

        return $this->entry($companyId, $depreciation->period->copy()->endOfMonth(), $depreciation->shop_id, 'Depreciation '.$depreciation->period->format('M Y').' — '.$assetName, null, [
            $this->debit($companyId, 'depreciation_expense', (float) $depreciation->amount),
            $this->credit($companyId, 'accumulated_depreciation', (float) $depreciation->amount),
        ]);
    }

    /**
     * Provident fund or income tax paid over: the payables it settles, out of
     * the money account it was paid from.
     */
    private function forStatutoryPayment(StatutoryPayment $payment): ?array
    {
        $moneyAccount = $payment->account_id ? Account::withoutGlobalScopes()->withTrashed()->find($payment->account_id) : null;

        if ($payment->status !== 'paid' || ! $moneyAccount) {
            return null;
        }

        $companyId = (int) $payment->company_id;
        $label = $payment->type === 'pf' ? 'Provident fund payment ' : 'Income tax payment ';

        return $this->entry($companyId, $payment->payment_date, $moneyAccount->shop_id, $label.$payment->period_from->format('M Y').'–'.$payment->period_to->format('M Y'), $payment->reference, [
            ...($payment->type === 'pf'
                ? [$this->debit($companyId, 'pf_payable', (float) $payment->employee_amount), $this->debit($companyId, 'pf_employer_payable', (float) $payment->employer_amount)]
                : [$this->debit($companyId, 'tds_payable', (float) $payment->amount)]),
            ['account' => $this->chart->forMoneyAccount($moneyAccount), 'credit' => (float) $payment->amount, 'shop_id' => $moneyAccount->shop_id],
        ]);
    }

    /**
     * The customer or supplier a money movement settles, and the shop whose
     * receivable or payable it settles (the sale's or purchase's shop).
     *
     * @return array{0: ?Model, 1: ?int}
     */
    private function partyAndShopOf(AccountTransaction $transaction, ?int $fallbackShopId): array
    {
        $source = $transaction->sourceable_type ? $transaction->sourceable_type::withoutGlobalScopes()->find($transaction->sourceable_id) : null;

        $document = match (true) {
            $source instanceof SalePayment => Sale::withoutGlobalScopes()->withTrashed()->find($source->sale_id),
            $source instanceof PurchasePayment => Purchase::withoutGlobalScopes()->withTrashed()->find($source->purchase_id),
            $source instanceof SaleReturn => Sale::withoutGlobalScopes()->withTrashed()->find($source->sale_id),
            $source instanceof PurchaseReturn => Purchase::withoutGlobalScopes()->withTrashed()->find($source->purchase_id),
            default => $source,
        };

        return match (true) {
            $document instanceof Sale => [$document->customer_id ? Customer::withoutGlobalScopes()->find($document->customer_id) : null, $document->shop_id],
            $document instanceof Purchase => [$document->supplier_id ? Supplier::withoutGlobalScopes()->find($document->supplier_id) : null, $document->shop_id],
            $document instanceof Customer, $document instanceof Supplier => [$document, $document->shop_id ?? $fallbackShopId],
            $document && method_exists($document, 'ledgerParty') => [$document->ledgerParty(), $fallbackShopId],
            default => [null, $fallbackShopId],
        };
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return array<string, mixed>
     */
    private function entry(int $companyId, $date, ?int $shopId, string $narration, ?string $reference, array $lines): array
    {
        return [
            'company_id' => $companyId,
            'date' => Carbon::parse($date ?? now())->toDateString(),
            'shop_id' => $shopId,
            'narration' => $narration,
            'reference' => $reference,
            'lines' => $lines,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function debit(int $companyId, string $key, float $amount, ?Model $party = null): array
    {
        return ['account' => $this->chart->account($companyId, $key), 'debit' => round($amount, 2), 'party' => $party];
    }

    /**
     * @return array<string, mixed>
     */
    private function credit(int $companyId, string $key, float $amount, ?Model $party = null): array
    {
        return ['account' => $this->chart->account($companyId, $key), 'credit' => round($amount, 2), 'party' => $party];
    }

    private function companyOfShop(?int $shopId): int
    {
        return (int) DB::table('shops')->where('id', $shopId)->value('company_id');
    }

    /**
     * What an entry posts, for comparing a posted entry with a wanted one.
     *
     * @param  JournalEntry|array<string, mixed>  $entry
     */
    private function signature(JournalEntry|array $entry): string
    {
        $lines = $entry instanceof JournalEntry
            ? $entry->lines->map(fn ($line) => [$line->ledger_account_id, $line->shop_id, $line->party_type, $line->party_id, round((float) $line->debit, 2), round((float) $line->credit, 2)])
            : collect($entry['lines'])->map(fn ($line) => [
                $line['account']->id,
                $line['shop_id'] ?? $entry['shop_id'],
                isset($line['party']) ? $line['party']::class : null,
                isset($line['party']) ? $line['party']->getKey() : null,
                round((float) ($line['debit'] ?? 0), 2),
                round((float) ($line['credit'] ?? 0), 2),
            ]);

        $date = $entry instanceof JournalEntry ? $entry->entry_date->toDateString() : $entry['date'];

        return $date.'|'.$lines->map(fn ($line) => implode(',', array_map(fn ($value) => (string) $value, $line)))->sort()->implode(';');
    }
}
