<?php

namespace Modules\Core\Support;

use Illuminate\Container\Attributes\Scoped;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Resolves the current tenant for the request: the user's active shop and
 * the company that shop belongs to. The company id is looked up once per
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

    public function companyId(): ?int
    {
        $shopId = $this->shopId();

        if (! $shopId) {
            return null;
        }

        if (! array_key_exists($shopId, $this->companyIdsByShop)) {
            $companyId = DB::table('shops')->where('id', $shopId)->value('company_id');
            $this->companyIdsByShop[$shopId] = $companyId ? (int) $companyId : null;
        }

        return $this->companyIdsByShop[$shopId];
    }

    public function forget(): void
    {
        $this->companyIdsByShop = [];
    }
}
