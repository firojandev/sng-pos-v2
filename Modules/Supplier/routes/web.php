<?php

use Illuminate\Support\Facades\Route;
use Modules\Supplier\Http\Controllers\SupplierController;

Route::middleware(['auth', 'permission:suppliers', 'feature:suppliers'])->group(function () {
    Route::resource('suppliers', SupplierController::class)->except(['show']);
});
