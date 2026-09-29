<?php

namespace Modules\Core\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

/**
 * Validation rules that respect tenancy. A plain `exists:table,id` rule
 * ignores the model's global scopes, so it would accept another company's
 * record; these rules only accept records the current company can use.
 */
class TenantRules
{
    /**
     * The record must belong to the current company (customers, suppliers,
     * expense categories).
     */
    public static function companyExists(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where('company_id', app(TenantContext::class)->companyId());
    }

    /**
     * The record must be one the current user can see: the shared
     * catalogue, the company's product bank, or the shop's own (products,
     * categories, brands, units, product models).
     */
    public static function catalogExists(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where(fn ($query) => CatalogVisibility::constrain($query, $table));
    }

    /**
     * The value must be unique among the current shop's products (each shop
     * keeps its own catalogue, so two shops may carry the same barcode).
     */
    public static function uniqueInShopCatalogue(string $column, ?int $ignoreProductId = null): Unique
    {
        $shopId = app(TenantContext::class)->shopId();

        return Rule::unique('products', $column)
            ->where(fn ($query) => $shopId ? $query->where('shop_id', $shopId) : $query->whereNull('shop_id'))
            ->ignore($ignoreProductId);
    }
}
