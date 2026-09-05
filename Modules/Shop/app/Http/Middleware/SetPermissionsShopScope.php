<?php

namespace Modules\Shop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPermissionsShopScope
{
    /**
     * Handle an incoming request and bind Spatie Permission team scope to the active shop.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = $request->user();

            if ($user->isSuperAdmin()) {
                setPermissionsTeamId(0);
            } else {
                $shopId = session('current_shop_id', $user->shop_id) ?? 0;
                setPermissionsTeamId($shopId);
            }
        }

        return $next($request);
    }
}
