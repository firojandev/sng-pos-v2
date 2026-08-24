<?php

use Illuminate\Support\Facades\Route;
use Modules\Employee\Http\Controllers\EmployeeController;

Route::middleware(['auth', 'permission:employees', 'feature:employees'])->group(function () {
    Route::resource('employees', EmployeeController::class)->except(['show']);
});
