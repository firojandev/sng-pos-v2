<?php

use Illuminate\Support\Facades\Route;
use Modules\Employee\Http\Controllers\EmployeeController;

Route::middleware(['auth', 'feature:employees'])->group(function () {
    Route::resource('employees', EmployeeController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:employees.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:employees.write')
        ->middlewareFor(['destroy'], 'permission:employees.delete');
});
