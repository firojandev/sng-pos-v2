<?php

namespace Modules\Accounting\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Accounting is run at company level: by a company's owner and admins in
 * the company workspace. Shop logins of a company's shops (shop admins and
 * their users) work the POS and don't get it; a standalone shop's owner,
 * who is their own company, does.
 */
class EnsureAccountingLevel
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $allowed = $user->shop_id
            ? (bool) $user->shop?->company?->isStandalone()
            : $user->isCompanyAdmin();

        abort_unless($allowed, 403, 'হিসাববিজ্ঞান কোম্পানির মালিক/এডমিনের জন্য (Accounting is for the company owner and admins)।');

        return $next($request);
    }
}
