<?php

namespace Modules\Purchase\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePurchaseDeliveryOrdersEnabled
{
    /**
     * Handle an incoming request.
     *
     * Abort with 403 Forbidden if the Purchase Delivery Orders feature is disabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('purchase.delivery_orders_enabled', false)) {
            abort(403, 'ডেলিভারি অর্ডার ফিচারটি বর্তমানে নিষ্ক্রিয় রয়েছে। / Purchase Delivery Orders feature is currently disabled.');
        }

        return $next($request);
    }
}
