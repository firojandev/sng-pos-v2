<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\ExpenseCategoryController;
use Modules\Finance\Http\Controllers\ExpenseController;
use Modules\Finance\Http\Controllers\IncomeController;

Route::middleware(['auth', 'feature:expense'])->group(function () {
    Route::resource('expense', ExpenseController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:expense.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:expense.write')
        ->middlewareFor(['destroy'], 'permission:expense.delete');

    Route::prefix('expense')->group(function () {
        Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show'])
            ->middlewareFor(['index'], 'permission:expense.view')
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:expense.write')
            ->middlewareFor(['destroy'], 'permission:expense.delete');
    });
});

Route::middleware(['auth', 'feature:income'])->group(function () {
    Route::resource('income', IncomeController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:income.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:income.write')
        ->middlewareFor(['destroy'], 'permission:income.delete');
});
