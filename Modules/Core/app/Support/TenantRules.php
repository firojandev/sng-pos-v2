<?php

namespace Modules\Core\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

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
     * The record must be the current company's own or part of the shared
     * catalogue (products, categories, brands, units, product models).
     */
    public static function catalogExists(string $table, string $column = 'id'): Exists
    {
        $companyId = app(TenantContext::class)->companyId();

        return Rule::exists($table, $column)->where(function ($query) use ($companyId) {
            $query->whereNull('company_id')->orWhere('company_id', $companyId);
        });
    }
}
