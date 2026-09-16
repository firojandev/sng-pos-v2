<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Modules\Auth\Http\Middleware\EnsureShopEmailIsVerified;
use Modules\Core\Http\Middleware\ConvertBengaliNumbers;
use Modules\Core\Http\Middleware\EnsureFeatureEnabled;
use Modules\Purchase\Http\Middleware\EnsurePurchaseDeliveryOrdersEnabled;
use Modules\Shop\Http\Middleware\EnsureSubscriptionActive;
use Modules\Shop\Http\Middleware\SetPermissionsShopScope;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(ConvertBengaliNumbers::class);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'feature' => EnsureFeatureEnabled::class,
            'delivery-orders.enabled' => EnsurePurchaseDeliveryOrdersEnabled::class,
            'verified' => EnsureShopEmailIsVerified::class,
        ]);

        $middleware->appendToGroup('web', [
            EnsureSubscriptionActive::class,
            EnsureShopEmailIsVerified::class,
            SetPermissionsShopScope::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvalidSignatureException $e, Request $request) {
            if ($request->routeIs('verification.verify')) {
                return response()->view('auth::verify-expired', [
                    'reason' => 'expired_signature',
                ], 403);
            }
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
