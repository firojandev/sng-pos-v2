<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\ExpenseCategoryController;
use Modules\Finance\Http\Controllers\ExpenseController;
use Modules\Finance\Http\Controllers\IncomeController;

Route::middleware(['auth', 'permission:expense', 'feature:expense'])->group(function () {
    Route::resource('expense', ExpenseController::class)->except(['show']);

    Route::prefix('expense')->group(function () {
        Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show']);
    });
});

Route::middleware(['auth', 'permission:income', 'feature:income'])->group(function () {
    Route::resource('income', IncomeController::class)->except(['show']);
});
