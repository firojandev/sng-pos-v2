<?php

use Illuminate\Support\Facades\Route;
use Modules\Shop\Http\Controllers\BranchController;
use Modules\Shop\Http\Controllers\PlanController;
use Modules\Shop\Http\Controllers\ShopController;
use Modules\Shop\Http\Controllers\SubscriptionController;
use Modules\Shop\Http\Controllers\WarehouseController;

Route::middleware(['auth', 'role:Super Admin'])->group(function () {
    Route::resource('shops', ShopController::class)->except(['show']);
    Route::resource('plans', PlanController::class)->except(['show']);

    Route::post('shops/{shop}/admins', [ShopController::class, 'storeAdmin'])->name('shops.admins.store');
    Route::delete('shops/{shop}/admins/{admin}', [ShopController::class, 'destroyAdmin'])->name('shops.admins.destroy');
    Route::put('shops/{shop}/subscription', [ShopController::class, 'updateSubscription'])->name('shops.subscription.update');
});

Route::middleware(['auth', 'feature:branches'])->group(function () {
    Route::resource('branches', BranchController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:branches.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:branches.write')
        ->middlewareFor(['destroy'], 'permission:branches.delete');
    Route::resource('warehouses', WarehouseController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:branches.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:branches.write')
        ->middlewareFor(['destroy'], 'permission:branches.delete');
});

Route::middleware(['auth'])->group(function () {
    Route::get('subscription', [SubscriptionController::class, 'show'])->name('subscription.show');
});
