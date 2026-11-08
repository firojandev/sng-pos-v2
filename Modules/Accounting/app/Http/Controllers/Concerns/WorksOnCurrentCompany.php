<?php

namespace Modules\Accounting\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Accounting\Services\ChartOfAccounts;
use Modules\Core\Support\TenantContext;
use Modules\Shop\Models\Shop;

trait WorksOnCurrentCompany
{
    protected function companyId(): int
    {
        $companyId = app(TenantContext::class)->companyId();
        abort_unless($companyId, 403, 'কোনো দোকান নির্বাচন করা নেই (No shop selected)।');

        app(ChartOfAccounts::class)->ensureFor($companyId);

        return $companyId;
    }

    /**
     * @return Collection<int, Shop>
     */
    protected function companyShops(): Collection
    {
        return Shop::where('company_id', $this->companyId())->orderBy('name')->get(['id', 'name']);
    }

    /**
     * The shop filter of a report: one of the company's shops, or all.
     */
    protected function shopFilter(Request $request): ?int
    {
        $shopId = $request->integer('shop_id') ?: null;

        return $shopId && $this->companyShops()->contains('id', $shopId) ? $shopId : null;
    }
}
