<?php

namespace Modules\Core\Support;

class Features
{
    /**
     * The full set of togglable shop features / permission keys.
     * Each key doubles as a Spatie permission name.
     *
     * @return array<string, array{bn: string, en: string}>
     */
    public static function all(): array
    {
        return [
            'sales' => ['bn' => 'বিক্রয়', 'en' => 'Sales'],
            'purchase' => ['bn' => 'ক্রয়', 'en' => 'Purchase'],
            'cashbox' => ['bn' => 'ক্যাশবক্স', 'en' => 'Cashbox'],
            'quick-sale' => ['bn' => 'দ্রুত বেচা', 'en' => 'Quick Sale'],
            'stock' => ['bn' => 'স্টক', 'en' => 'Stock'],
            'products' => ['bn' => 'পণ্য ব্যবস্থাপনা', 'en' => 'Product Management'],
            'branches' => ['bn' => 'শাখা ও গুদাম', 'en' => 'Branches & Warehouses'],
            'customers' => ['bn' => 'গ্রাহক', 'en' => 'Customers'],
            'suppliers' => ['bn' => 'সরবরাহকারী', 'en' => 'Suppliers'],
            'income' => ['bn' => 'আয়', 'en' => 'Income'],
            'expense' => ['bn' => 'ব্যয়', 'en' => 'Expense'],
            'accounts' => ['bn' => 'অ্যাকাউন্ট', 'en' => 'Accounts'],
            'account-transfers' => ['bn' => 'ফান্ড ট্রান্সফার', 'en' => 'Fund Transfers'],
            'assets' => ['bn' => 'সম্পদ', 'en' => 'Assets'],
            'debts' => ['bn' => 'দেনা', 'en' => 'Debts'],
            'lend' => ['bn' => 'ধার (পাওনা)', 'en' => 'Lend'],
            'security-money' => ['bn' => 'জামানত', 'en' => 'Security Money'],
            'tax' => ['bn' => 'ট্যাক্স ও ভ্যাট', 'en' => 'Tax & VAT'],
            'report-sales' => ['bn' => 'বিক্রয় রিপোর্ট', 'en' => 'Sales Report'],
            'report-sales-vat' => ['bn' => 'বিক্রয় ভ্যাট রিপোর্ট', 'en' => 'Sales VAT Report'],
            'report-purchase' => ['bn' => 'ক্রয় রিপোর্ট', 'en' => 'Purchase Report'],
            'report-stock' => ['bn' => 'স্টক রিপোর্ট', 'en' => 'Stock Report'],
            'report-products' => ['bn' => 'পণ্য রিপোর্ট', 'en' => 'Product Report'],
            'report-product-profit-loss' => ['bn' => 'পণ্যভিত্তিক লাভ-ক্ষতি রিপোর্ট', 'en' => 'Product Wise Profit & Loss Report'],
            'report-profit-loss' => ['bn' => 'লাভ-ক্ষতি রিপোর্ট', 'en' => 'Profit & Loss Report'],
            'report-income' => ['bn' => 'আয় রিপোর্ট', 'en' => 'Income Report'],
            'report-expense' => ['bn' => 'ব্যয় রিপোর্ট', 'en' => 'Expense Report'],
            'report-financial-position' => ['bn' => 'আর্থিক অবস্থান রিপোর্ট', 'en' => 'Financial Position Report'],
            'report-balance-sheet' => ['bn' => 'ব্যালেন্স শীট', 'en' => 'Balance Sheet'],
            'audit' => ['bn' => 'অ্যাক্টিভিটি লগ', 'en' => 'Audit Log'],
            'employees' => ['bn' => 'কর্মচারী', 'en' => 'Employees'],
            'users' => ['bn' => 'ইউজার', 'en' => 'Users'],
            'printer-settings' => ['bn' => 'প্রিন্টার সেটিংস', 'en' => 'Printer Settings'],
            'whatsapp-settings' => ['bn' => 'হোয়াটসঅ্যাপ সেটিংস', 'en' => 'WhatsApp Settings'],
            'subscription' => ['bn' => 'সাবস্ক্রিপশন ও প্ল্যান', 'en' => 'Subscription & Plan'],
            'backup' => ['bn' => 'ডাটাবেজ ব্যাকআপ', 'en' => 'Database Backup'],
        ];
    }

    /**
     * @return string[]
     */
    public static function keys(): array
    {
        return array_keys(static::all());
    }
}
