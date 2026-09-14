<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShopEmailIsVerified
{
    /**
     * Prevent shop owners from accessing the application without verifying their email address.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow unauthenticated requests to continue to guest/auth middleware
        if (! $user) {
            return $next($request);
        }

        // Super Admins are exempt
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Allow verification routes, logout, login, and home
        if ($request->routeIs('verification.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Users without an email address (e.g. phone-only shop created by Super Admin) are exempt
        if (empty($user->email)) {
            return $next($request);
        }

        // If the user is a shop owner and hasn't verified their email, deny access
        if ($user->isShopOwner() && ! $user->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'দোকান অ্যাক্সেস করার পূর্বে আপনার ইমেইল ভেরিফাই করা আবশ্যক।',
                    'email_verified' => false,
                ], 403);
            }

            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
