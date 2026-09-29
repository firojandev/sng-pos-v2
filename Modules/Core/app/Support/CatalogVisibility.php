<?php

namespace Modules\Core\Support;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Which catalogue records (products, product categories, brands, units,
 * product models) the current user can see. The catalogue is per shop:
 *
 * - a shop sees its own records, plus older records from before the
 *   catalogue was per shop that several shops still use (its company's
 *   without a shop; shared ones, company_id null);
 * - no other shop's records, even in the same company;
 * - the company workspace (no shop) sees its company's records, read only.
 *
 * Expense categories share the categories table but stay company-wide.
 */
class CatalogVisibility
{
    public static function constrain(Builder $query, string $table): void
    {
        $user = Auth::user();

        if (! $user || $user->isSuperAdmin()) {
            return;
        }

        $tenant = app(TenantContext::class);
        $companyId = $tenant->companyId();
        $shopId = $tenant->shopId();

        $query->where(function ($query) use ($table, $companyId, $shopId) {
            $query->whereNull("{$table}.company_id");

            if (! $companyId) {
                return;
            }

            $query->orWhere(function ($query) use ($table, $companyId, $shopId) {
                $query->where("{$table}.company_id", $companyId);

                if ($shopId) {
                    $query->where(function ($query) use ($table, $shopId) {
                        $query->where("{$table}.shop_id", $shopId)->orWhereNull("{$table}.shop_id");

                        if ($table === 'categories') {
                            $query->orWhere('categories.type', '!=', 'product');
                        }
                    });
                }
            });
        });
    }
}
