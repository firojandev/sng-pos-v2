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
            'stock' => ['bn' => 'স্টক', 'en' => 'Stock'],
            'products' => ['bn' => 'পণ্য ব্যবস্থাপনা', 'en' => 'Product Management'],
            'customers' => ['bn' => 'গ্রাহক', 'en' => 'Customers'],
            'suppliers' => ['bn' => 'সরবরাহকারী', 'en' => 'Suppliers'],
            'income' => ['bn' => 'আয়', 'en' => 'Income'],
            'expense' => ['bn' => 'ব্যয়', 'en' => 'Expense'],
            'tax' => ['bn' => 'ট্যাক্স ও ভ্যাট', 'en' => 'Tax & VAT'],
            'reports' => ['bn' => 'রিপোর্ট', 'en' => 'Reports'],
            'employees' => ['bn' => 'কর্মচারী', 'en' => 'Employees'],
            'users' => ['bn' => 'ইউজার', 'en' => 'Users'],
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
