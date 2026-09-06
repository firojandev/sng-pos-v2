<?php

namespace Modules\Core\Support;

class Permissions
{
    /**
     * The standard CRUD actions available for most features.
     *
     * @return string[]
     */
    public static function standardActions(): array
    {
        return ['view', 'create', 'edit', 'delete'];
    }

    /**
     * Map of feature => allowed actions.
     *
     * @return array<string, string[]>
     */
    public static function featureActions(): array
    {
        return [
            'sales' => ['view', 'create', 'edit', 'delete', 'return', 'print'],
            'purchase' => ['view', 'create', 'edit', 'delete', 'receive', 'return', 'print'],
            'quick-sale' => ['view', 'create'],
            'stock' => ['view', 'create', 'edit', 'delete', 'adjust', 'transfer'],
            'products' => ['view', 'create', 'edit', 'delete'],
            'branches' => ['view', 'create', 'edit', 'delete'],
            'customers' => ['view', 'create', 'edit', 'delete', 'payment'],
            'suppliers' => ['view', 'create', 'edit', 'delete', 'payment'],
            'income' => ['view', 'create', 'edit', 'delete'],
            'expense' => ['view', 'create', 'edit', 'delete'],
            'accounts' => ['view', 'create', 'edit', 'delete', 'transfer'],
            'account-transfers' => ['view', 'create', 'delete'],
            'tax' => ['view', 'edit'],
            'reports' => ['view', 'print'],
            'audit' => ['view'],
            'employees' => ['view', 'create', 'edit', 'delete'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'cashbox' => ['view', 'cash-in', 'cash-out'],
        ];
    }

    /**
     * Human-readable bilingual labels for every action.
     *
     * @return array<string, array{bn: string, en: string}>
     */
    public static function actionLabels(): array
    {
        return [
            'view' => ['bn' => 'দেখা', 'en' => 'View'],
            'create' => ['bn' => 'নতুন তৈরি', 'en' => 'Create'],
            'edit' => ['bn' => 'সম্পাদনা', 'en' => 'Edit'],
            'delete' => ['bn' => 'মুছে ফেলা', 'en' => 'Delete'],
            'print' => ['bn' => 'প্রিন্ট / রিপোর্ট', 'en' => 'Print / Report'],
            'return' => ['bn' => 'পণ্য ফেরত', 'en' => 'Return'],
            'receive' => ['bn' => 'পণ্য গ্রহণ', 'en' => 'Receive'],
            'payment' => ['bn' => 'বাকি জমা / লেনদেন', 'en' => 'Due Payment'],
            'adjust' => ['bn' => 'স্টক সমন্বয়', 'en' => 'Stock Adjust'],
            'transfer' => ['bn' => 'স্থানান্তর', 'en' => 'Transfer'],
            'cash-in' => ['bn' => 'ক্যাশ ইন', 'en' => 'Cash In'],
            'cash-out' => ['bn' => 'ক্যাশ আউট', 'en' => 'Cash Out'],
        ];
    }

    /**
     * Get the actions permitted for a specific feature.
     *
     * @return string[]
     */
    public static function actionsFor(string $feature): array
    {
        return static::featureActions()[$feature] ?? static::standardActions();
    }

    /**
     * Default standard actions for backwards compatibility.
     *
     * @return string[]
     */
    public static function actions(): array
    {
        return static::standardActions();
    }

    /**
     * The "{feature}.{action}" permission names for a single feature.
     *
     * @return string[]
     */
    public static function for(string $feature): array
    {
        return array_map(fn (string $action) => "{$feature}.{$action}", static::actionsFor($feature));
    }

    /**
     * Every "{feature}.{action}" permission name across all features.
     *
     * @return string[]
     */
    public static function all(): array
    {
        return array_merge(...array_map(
            fn (string $feature) => static::for($feature),
            Features::keys()
        ));
    }
}
