<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;

Route::middleware(['auth', 'permission:customers', 'feature:customers'])->group(function () {
    Route::resource('customers', CustomerController::class)->except(['show']);
});
