<?php

use Illuminate\Support\Facades\Route;
use Modules\Shop\Http\Controllers\ShopController;

Route::middleware(['auth', 'role:Super Admin'])->group(function () {
    Route::resource('shops', ShopController::class)->except(['show']);

    Route::post('shops/{shop}/admins', [ShopController::class, 'storeAdmin'])->name('shops.admins.store');
    Route::delete('shops/{shop}/admins/{admin}', [ShopController::class, 'destroyAdmin'])->name('shops.admins.destroy');
});
