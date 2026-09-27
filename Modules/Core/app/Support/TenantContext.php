<?php

namespace Modules\Core\Support;

use Illuminate\Container\Attributes\Scoped;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Resolves the current tenant for the request: the user's active shop and
 * the company that shop belongs to; with no shop, a company-level user's
 * company (the company workspace). The company id is looked up once per
 * shop and reused, so company-scoped queries don't hit the shops table
 * every time.
 */
#[Scoped]
class TenantContext
{
    /**
     * @var array<int, int|null> shop id => company id
     */
    private array $companyIdsByShop = [];

    public function shopId(): ?int
    {
        $shopId = Auth::user()?->shop_id;

        return $shopId ? (int) $shopId : null;
    }

    /**
     * @var array<int, int|null> user id => company id (company workspace)
     */
    private array $workspaceCompanyIds = [];

    public function companyId(): ?int
    {
        $shopId = $this->shopId();

        // No shop: a company-level user works in their company's workspace.
        if (! $shopId) {
            $user = Auth::user();

            if (! $user || $user->isSuperAdmin()) {
                return null;
            }

            if (! array_key_exists($user->id, $this->workspaceCompanyIds)) {
                $this->workspaceCompanyIds[$user->id] = $user->companyLevelCompany()?->id;
            }

            return $this->workspaceCompanyIds[$user->id];
        }

        if (! array_key_exists($shopId, $this->companyIdsByShop)) {
            $companyId = DB::table('shops')->where('id', $shopId)->value('company_id');
            $this->companyIdsByShop[$shopId] = $companyId ? (int) $companyId : null;
        }

        return $this->companyIdsByShop[$shopId];
    }

    /**
     * The shops whose data the user sees: their shop, or in the company
     * workspace every shop of their company.
     *
     * @return list<int>
     */
    public function visibleShopIds(): array
    {
        if ($shopId = $this->shopId()) {
            return [$shopId];
        }

        $companyId = $this->companyId();

        return $companyId ? DB::table('shops')->where('company_id', $companyId)->pluck('id')->map(fn ($id) => (int) $id)->all() : [];
    }

    public function forget(): void
    {
        $this->companyIdsByShop = [];
        $this->workspaceCompanyIds = [];
    }
}
