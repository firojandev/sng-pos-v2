<?php

namespace Modules\Shop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    /**
     * Block shop users once their subscription is suspended, cancelled, or
     * past its trial/grace period. Super Admins, guests, shops with no
     * subscription record yet, and the subscription page itself are always
     * let through so nobody gets locked out with no way to see why.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->shop_id || $user->isSuperAdmin()) {
            return $next($request);
        }

        if ($request->routeIs('subscription.show') || $request->routeIs('logout')) {
            return $next($request);
        }

        $subscription = $user->shop?->subscription;

        if (! $subscription || $subscription->isUsable()) {
            return $next($request);
        }

        return redirect()->route('subscription.show');
    }
}
