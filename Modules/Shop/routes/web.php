<?php

use Illuminate\Support\Facades\Route;
use Modules\Shop\Http\Controllers\BranchController;
use Modules\Shop\Http\Controllers\PlanController;
use Modules\Shop\Http\Controllers\ShopController;
use Modules\Shop\Http\Controllers\ShopSelectionController;
use Modules\Shop\Http\Controllers\ShopSettingsController;
use Modules\Shop\Http\Controllers\SubscriptionController;
use Modules\Shop\Http\Controllers\SystemSettingsController;
use Modules\Shop\Http\Controllers\WarehouseController;

Route::middleware(['auth', 'role:Super Admin'])->group(function () {
    Route::get('shops/check-availability', [ShopController::class, 'checkAvailability'])->name('shops.check-availability');
    Route::resource('shops', ShopController::class);
    Route::resource('plans', PlanController::class)->except(['show']);

    Route::post('shops/{shop}/admins', [ShopController::class, 'storeAdmin'])->name('shops.admins.store');
    Route::delete('shops/{shop}/admins/{admin}', [ShopController::class, 'destroyAdmin'])->name('shops.admins.destroy');
    Route::put('shops/{shop}/subscription', [ShopController::class, 'updateSubscription'])->name('shops.subscription.update');

    Route::get('system-settings', [SystemSettingsController::class, 'index'])->name('system-settings.index');
    Route::post('system-settings', [SystemSettingsController::class, 'update'])->name('system-settings.update');
    Route::post('system-settings/toggle-landing', [SystemSettingsController::class, 'toggleLanding'])->name('system-settings.toggle-landing');
    Route::post('system-settings/toggle-registration', [SystemSettingsController::class, 'toggleRegistration'])->name('system-settings.toggle-registration');
    Route::post('system-settings/toggle-terms-policy', [SystemSettingsController::class, 'toggleTermsAndPolicy'])->name('system-settings.toggle-terms-policy');
    Route::post('system-settings/toggle-credit-text', [SystemSettingsController::class, 'toggleCreditText'])->name('system-settings.toggle-credit-text');
});

Route::middleware(['auth', 'feature:branches'])->group(function () {
    Route::resource('branches', BranchController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:branches.view')
        ->middlewareFor(['create', 'store'], 'permission:branches.create')
        ->middlewareFor(['edit', 'update'], 'permission:branches.edit')
        ->middlewareFor(['destroy'], 'permission:branches.delete');
    Route::post('warehouses/{warehouse}/set-default', [WarehouseController::class, 'setDefault'])
        ->name('warehouses.set-default')
        ->middleware('permission:branches.edit');
    Route::resource('warehouses', WarehouseController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:branches.view')
        ->middlewareFor(['create', 'store'], 'permission:branches.create')
        ->middlewareFor(['edit', 'update'], 'permission:branches.edit')
        ->middlewareFor(['destroy'], 'permission:branches.delete');
});

Route::middleware(['auth'])->group(function () {
    Route::get('select-shop', [ShopSelectionController::class, 'index'])->name('shops.select');
    Route::post('select-shop/{shop}', [ShopSelectionController::class, 'select'])->name('shops.switch');
    Route::get('subscription', [SubscriptionController::class, 'show'])
        ->name('subscription.show')
        ->middleware('feature:subscription');
    Route::get('settings', [ShopSettingsController::class, 'edit'])->name('settings.index');
    Route::put('settings', [ShopSettingsController::class, 'update'])->name('settings.update');
});
