<?php

namespace Modules\Accounting\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\FiscalYear;
use Modules\Accounting\Models\LedgerAccount;
use Modules\Company\Models\Company;
use Modules\Finance\Models\Account;

/**
 * The company's chart of accounts. Every company gets the standard accounts
 * below (identified by a stable system_key that the app posts to), and each
 * cash, bank or mobile-banking account gets its own ledger account under
 * "Cash & Bank".
 */
class ChartOfAccounts
{
    /**
     * [code, name, type, system_key, parent code, is_group]
     *
     * @var list<array{0: string, 1: string, 2: string, 3: string, 4: ?string, 5: bool}>
     */
    private const DEFAULT_ACCOUNTS = [
        ['1000', 'সম্পদ (Assets)', 'asset', 'assets', null, true],
        ['1100', 'নগদ ও ব্যাংক (Cash & Bank)', 'asset', 'cash_and_bank', '1000', true],
        ['1200', 'গ্রাহকের কাছে পাওনা (Accounts Receivable)', 'asset', 'accounts_receivable', '1000', false],
        ['1300', 'মজুদ পণ্য (Inventory)', 'asset', 'inventory', '1000', false],
        ['1400', 'কর্মচারী অগ্রিম (Employee Advances)', 'asset', 'employee_advances', '1000', false],
        ['1500', 'প্রদত্ত ঋণ (Loans Given)', 'asset', 'loans_receivable', '1000', false],
        ['1600', 'প্রদত্ত জামানত (Security Deposits Paid)', 'asset', 'security_deposits_paid', '1000', false],
        ['1700', 'স্থায়ী সম্পদ (Fixed Assets)', 'asset', 'fixed_assets', '1000', false],
        ['1750', 'পুঞ্জীভূত অবচয় (Accumulated Depreciation)', 'asset', 'accumulated_depreciation', '1000', false],

        ['2000', 'দায় (Liabilities)', 'liability', 'liabilities', null, true],
        ['2100', 'সরবরাহকারীর পাওনা (Accounts Payable)', 'liability', 'accounts_payable', '2000', false],
        ['2200', 'ভ্যাট প্রদেয় (VAT Payable)', 'liability', 'vat_payable', '2000', false],
        ['2300', 'বেতন প্রদেয় (Salary Payable)', 'liability', 'salary_payable', '2000', false],
        ['2310', 'কর্মচারীর পিএফ প্রদেয় (Employee PF Payable)', 'liability', 'pf_payable', '2000', false],
        ['2315', 'মালিকের পিএফ প্রদেয় (Employer PF Payable)', 'liability', 'pf_employer_payable', '2000', false],
        ['2320', 'আয়কর প্রদেয় — উৎসে কর (Income Tax Payable)', 'liability', 'tds_payable', '2000', false],
        ['2400', 'গৃহীত ঋণ (Loans Taken)', 'liability', 'loans_payable', '2000', false],
        ['2500', 'গৃহীত জামানত (Security Deposits Received)', 'liability', 'security_deposits_received', '2000', false],
        ['2600', 'লয়্যালটি পয়েন্ট দায় (Loyalty Points Liability)', 'liability', 'loyalty_liability', '2000', false],

        ['3000', 'মালিকানা স্বত্ব (Equity)', 'equity', 'equity', null, true],
        ['3100', 'মালিকের মূলধন (Owner\'s Capital)', 'equity', 'owners_capital', '3000', false],
        ['3200', 'প্রারম্ভিক ব্যালেন্স ইকুইটি (Opening Balance Equity)', 'equity', 'opening_balance_equity', '3000', false],
        ['3300', 'সংরক্ষিত মুনাফা (Retained Earnings)', 'equity', 'retained_earnings', '3000', false],

        ['4000', 'আয় (Income)', 'income', 'income', null, true],
        ['4100', 'বিক্রয় (Sales)', 'income', 'sales', '4000', false],
        ['4150', 'বিক্রয় ফেরত (Sales Returns)', 'income', 'sales_returns', '4000', false],
        ['4200', 'ডেলিভারি চার্জ আয় (Delivery Charge Income)', 'income', 'delivery_income', '4000', false],
        ['4300', 'অন্যান্য আয় (Other Income)', 'income', 'other_income', '4000', false],

        ['5000', 'ব্যয় (Expenses)', 'expense', 'expenses', null, true],
        ['5100', 'বিক্রিত পণ্যের ব্যয় (Cost of Goods Sold)', 'expense', 'cogs', '5000', false],
        ['5200', 'বেতন ও মজুরি (Salary & Wages)', 'expense', 'salary_expense', '5000', false],
        ['5210', 'নিয়োগকর্তার পিএফ অবদান (Employer PF Contribution)', 'expense', 'pf_expense', '5000', false],
        ['5300', 'অবচয় ব্যয় (Depreciation Expense)', 'expense', 'depreciation_expense', '5000', false],
        ['5400', 'সাধারণ ব্যয় (General Expenses)', 'expense', 'general_expenses', '5000', false],
        ['5500', 'লয়্যালটি পয়েন্ট ব্যয় (Loyalty Points Expense)', 'expense', 'loyalty_expense', '5000', false],
        ['5600', 'স্টক সমন্বয় ক্ষতি (Stock Adjustment Loss)', 'expense', 'stock_adjustment_loss', '5000', false],
        ['5700', 'প্রদত্ত ছাড় (Discounts Allowed)', 'expense', 'discounts_allowed', '5000', false],
    ];

    /**
     * Create any missing standard accounts and the current fiscal year.
     */
    public function ensureFor(int $companyId): void
    {
        $standardKeys = array_column(self::DEFAULT_ACCOUNTS, 3);
        $existing = LedgerAccount::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereIn('system_key', $standardKeys)
            ->count();

        if ($existing === count($standardKeys)) {
            $this->ensureCurrentFiscalYear($companyId);

            return;
        }

        DB::transaction(function () use ($companyId) {
            $idsByCode = [];

            foreach (self::DEFAULT_ACCOUNTS as [$code, $name, $type, $systemKey, $parentCode, $isGroup]) {
                $account = LedgerAccount::withoutGlobalScopes()->firstOrCreate(
                    ['company_id' => $companyId, 'system_key' => $systemKey],
                    [
                        'code' => $code,
                        'name' => $name,
                        'type' => $type,
                        'is_group' => $isGroup,
                        'parent_id' => $parentCode ? $idsByCode[$parentCode] : null,
                    ],
                );

                $idsByCode[$code] = $account->id;
            }
        });

        $this->ensureCurrentFiscalYear($companyId);
    }

    public function account(int $companyId, string $systemKey): LedgerAccount
    {
        $this->ensureFor($companyId);

        return LedgerAccount::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('system_key', $systemKey)
            ->firstOrFail();
    }

    /**
     * The ledger account of a cash, bank or mobile-banking account, created
     * under "Cash & Bank" the first time it is needed.
     */
    public function forMoneyAccount(Account $moneyAccount): LedgerAccount
    {
        if ($moneyAccount->ledger_account_id) {
            $linked = LedgerAccount::withoutGlobalScopes()->find($moneyAccount->ledger_account_id);

            if ($linked) {
                return $linked;
            }
        }

        $companyId = (int) DB::table('shops')->where('id', $moneyAccount->shop_id)->value('company_id');
        $group = $this->account($companyId, 'cash_and_bank');
        $shopName = DB::table('shops')->where('id', $moneyAccount->shop_id)->value('name');
        $companyShops = DB::table('shops')->where('company_id', $companyId)->count();

        $ledger = LedgerAccount::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'parent_id' => $group->id,
            'code' => $group->code.'-'.$moneyAccount->id,
            'name' => $moneyAccount->name.($companyShops > 1 && $shopName ? ' — '.$shopName : ''),
            'type' => 'asset',
            'system_key' => 'money_account_'.$moneyAccount->id,
            'allow_manual_posting' => true,
        ]);

        Account::withoutGlobalScopes()->whereKey($moneyAccount->id)->update(['ledger_account_id' => $ledger->id]);
        $moneyAccount->ledger_account_id = $ledger->id;

        return $ledger;
    }

    /**
     * The fiscal year containing today, following the company's start month.
     */
    public function ensureCurrentFiscalYear(int $companyId, ?Carbon $date = null): FiscalYear
    {
        $date ??= now();
        $startMonth = (int) (Company::withTrashed()->whereKey($companyId)->value('fiscal_year_start_month') ?: 7);
        $startsOn = Carbon::create($date->year, $startMonth, 1)->startOfDay();

        if ($startsOn->greaterThan($date)) {
            $startsOn->subYear();
        }

        $endsOn = $startsOn->copy()->addYear()->subDay();

        $existing = FiscalYear::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereDate('starts_on', $startsOn->toDateString())
            ->first();

        return $existing ?? FiscalYear::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'starts_on' => $startsOn->toDateString(),
            'ends_on' => $endsOn->toDateString(),
            'name' => $startsOn->month === 1 ? (string) $startsOn->year : $startsOn->year.'-'.$endsOn->format('y'),
        ]);
    }
}
