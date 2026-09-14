<?php

use Illuminate\Support\Facades\Route;
use Modules\FinanceManagement\Http\Controllers\AssetController;
use Modules\FinanceManagement\Http\Controllers\DebtController;
use Modules\FinanceManagement\Http\Controllers\LendController;
use Modules\FinanceManagement\Http\Controllers\SecurityMoneyController;

Route::middleware(['auth', 'feature:assets'])->group(function () {
    Route::resource('assets', AssetController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:assets.view')
        ->middlewareFor(['create', 'store'], 'permission:assets.create')
        ->middlewareFor(['edit', 'update'], 'permission:assets.edit')
        ->middlewareFor(['destroy'], 'permission:assets.delete');
});

Route::middleware(['auth', 'feature:debts'])->group(function () {
    Route::resource('debts', DebtController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:debts.view')
        ->middlewareFor(['create', 'store'], 'permission:debts.create')
        ->middlewareFor(['edit', 'update'], 'permission:debts.edit')
        ->middlewareFor(['destroy'], 'permission:debts.delete');
});

Route::middleware(['auth', 'feature:lend'])->group(function () {
    Route::resource('lend', LendController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:lend.view')
        ->middlewareFor(['create', 'store'], 'permission:lend.create')
        ->middlewareFor(['edit', 'update'], 'permission:lend.edit')
        ->middlewareFor(['destroy'], 'permission:lend.delete');
});

Route::middleware(['auth', 'feature:security-money'])->group(function () {
    Route::resource('security-money', SecurityMoneyController::class)
        ->parameters(['security-money' => 'securityMoney'])
        ->except(['show'])
        ->middlewareFor(['index'], 'permission:security-money.view')
        ->middlewareFor(['create', 'store'], 'permission:security-money.create')
        ->middlewareFor(['edit', 'update'], 'permission:security-money.edit')
        ->middlewareFor(['destroy'], 'permission:security-money.delete');
});
