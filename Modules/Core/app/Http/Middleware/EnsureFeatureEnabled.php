<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        if (! $user || ! $user->shop || ! $user->shop->hasFeature($feature)) {
            abort(403, 'এই ফিচারটি আপনার দোকানের জন্য সক্রিয় নেই।');
        }

        return $next($request);
    }
}
