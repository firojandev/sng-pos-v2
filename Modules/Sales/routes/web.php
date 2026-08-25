<?php

use Illuminate\Support\Facades\Route;
use Modules\Sales\Http\Controllers\SaleController;

Route::middleware(['auth', 'permission:sales', 'feature:sales'])->group(function () {
    Route::resource('sales', SaleController::class)->except(['show']);
});
