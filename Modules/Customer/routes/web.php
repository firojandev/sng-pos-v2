<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;

Route::middleware(['auth', 'feature:customers'])->group(function () {
    Route::resource('customers', CustomerController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:customers.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:customers.write')
        ->middlewareFor(['destroy'], 'permission:customers.delete');
});
