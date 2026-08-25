<?php

use Illuminate\Support\Facades\Route;
use Modules\Purchase\Http\Controllers\PurchaseController;

Route::middleware(['auth', 'permission:purchase', 'feature:purchase'])->group(function () {
    Route::resource('purchase', PurchaseController::class)->except(['show']);
});
