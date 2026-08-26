<?php

use Illuminate\Support\Facades\Route;
use Modules\Sales\Http\Controllers\QuickSaleController;
use Modules\Sales\Http\Controllers\SaleController;

Route::middleware(['auth', 'permission:sales', 'feature:sales'])->group(function () {
    Route::get('sales/ledger', [SaleController::class, 'ledger'])->name('sales.ledger');
    Route::resource('sales', SaleController::class)->except(['show']);
});

Route::middleware(['auth', 'permission:quick-sale', 'feature:quick-sale'])->prefix('quick-sale')->name('quick-sale.')->group(function () {
    Route::get('/', [QuickSaleController::class, 'create'])->name('create');
    Route::post('/', [QuickSaleController::class, 'store'])->name('store');
    Route::get('/customers/search', [QuickSaleController::class, 'searchCustomers'])->name('customers.search');
});
