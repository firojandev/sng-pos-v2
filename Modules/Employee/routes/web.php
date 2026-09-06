<?php

use Illuminate\Support\Facades\Route;
use Modules\Employee\Http\Controllers\EmployeeController;

Route::middleware(['auth', 'feature:employees'])->group(function () {
    Route::resource('employees', EmployeeController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:employees.view')
        ->middlewareFor(['create', 'store'], 'permission:employees.create')
        ->middlewareFor(['edit', 'update'], 'permission:employees.edit')
        ->middlewareFor(['destroy'], 'permission:employees.delete');
});
