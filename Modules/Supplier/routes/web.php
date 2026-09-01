<?php

use Illuminate\Support\Facades\Route;
use Modules\Supplier\Http\Controllers\SupplierController;

Route::middleware(['auth', 'feature:suppliers'])->group(function () {
    Route::resource('suppliers', SupplierController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:suppliers.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:suppliers.write')
        ->middlewareFor(['destroy'], 'permission:suppliers.delete');
});
