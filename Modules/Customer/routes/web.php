<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;

Route::middleware(['auth', 'feature:customers'])->group(function () {
    Route::get('customers/metrics', [CustomerController::class, 'metrics'])
        ->name('customers.metrics')
        ->middleware('permission:customers.view');

    Route::resource('customers', CustomerController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:customers.view')
        ->middlewareFor(['create', 'store'], 'permission:customers.create')
        ->middlewareFor(['edit', 'update'], 'permission:customers.edit')
        ->middlewareFor(['destroy'], 'permission:customers.delete');
});
