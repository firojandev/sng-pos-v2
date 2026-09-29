<?php

namespace Modules\Product\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Product management belongs to a shop: its owner and the users the owner
 * creates. Outside a shop (the Super Admin, the company workspace) there is
 * no catalogue to manage.
 */
class EnsureShopCatalogue
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless((bool) $request->user()?->shop_id, 403, 'পণ্য ব্যবস্থাপনা শুধুমাত্র দোকানের ভেতরে (Product management is done inside a shop)।');

        return $next($request);
    }
}
