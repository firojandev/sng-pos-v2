<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\AccountController;
use Modules\Finance\Http\Controllers\AccountTransferController;
use Modules\Finance\Http\Controllers\ExpenseCategoryController;
use Modules\Finance\Http\Controllers\ExpenseController;
use Modules\Finance\Http\Controllers\ExpenseSubCategoryController;
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

        Route::resource('expense-sub-categories', ExpenseSubCategoryController::class)
            ->parameters(['expense-sub-categories' => 'expenseSubCategory'])
            ->except(['show'])
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

Route::middleware(['auth', 'feature:accounts'])->group(function () {
    Route::post('accounts/{account}/set-default', [AccountController::class, 'setDefault'])
        ->name('accounts.set-default')
        ->middleware('permission:accounts.write');

    Route::get('accounts/{account}/ledger', [AccountController::class, 'ledger'])
        ->name('accounts.ledger')
        ->middleware('permission:accounts.view');

    Route::resource('accounts', AccountController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:accounts.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:accounts.write')
        ->middlewareFor(['destroy'], 'permission:accounts.delete');
});

Route::middleware(['auth', 'feature:account-transfers'])->group(function () {
    Route::resource('account-transfers', AccountTransferController::class)
        ->parameters(['account-transfers' => 'accountTransfer'])
        ->except(['show', 'edit', 'update'])
        ->middlewareFor(['index'], 'permission:account-transfers.view')
        ->middlewareFor(['create', 'store'], 'permission:account-transfers.write')
        ->middlewareFor(['destroy'], 'permission:account-transfers.delete');
});
