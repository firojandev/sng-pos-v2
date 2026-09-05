<?php

use Illuminate\Support\Facades\Route;
use Modules\Sales\Http\Controllers\QuickSaleController;
use Modules\Sales\Http\Controllers\SaleController;
use Modules\Sales\Http\Controllers\SaleReturnController;

Route::middleware(['auth', 'feature:sales'])->group(function () {
    Route::get('sales/ledger/print', [SaleController::class, 'printLedger'])->name('sales.ledger.print')->middleware('permission:sales.view');
    Route::get('sales/ledger', [SaleController::class, 'ledger'])->name('sales.ledger')->middleware('permission:sales.view');
    Route::get('sales/{sale}/invoice-modal', [SaleController::class, 'invoiceModal'])->name('sales.invoice-modal')->middleware('permission:sales.view');
    Route::get('sales/{sale}/print-invoice', [SaleController::class, 'printInvoice'])->name('sales.print-invoice')->middleware('permission:sales.view');
    Route::get('sale-returns', [SaleReturnController::class, 'index'])->name('sale-returns.index')->middleware('permission:sales.view');
    Route::get('sales/{sale}/returns/create', [SaleReturnController::class, 'create'])->name('sale-returns.create')->middleware('permission:sales.write');
    Route::post('sales/{sale}/returns', [SaleReturnController::class, 'store'])->name('sale-returns.store')->middleware('permission:sales.write');
    Route::resource('sales', SaleController::class)
        ->middlewareFor(['index', 'show'], 'permission:sales.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:sales.write')
        ->middlewareFor(['destroy'], 'permission:sales.delete');
});

Route::middleware(['auth', 'feature:quick-sale'])->prefix('quick-sale')->name('quick-sale.')->group(function () {
    Route::get('/', [QuickSaleController::class, 'create'])->name('create')->middleware('permission:quick-sale.write');
    Route::post('/', [QuickSaleController::class, 'store'])->name('store')->middleware('permission:quick-sale.write');
    Route::get('/customers/search', [QuickSaleController::class, 'searchCustomers'])->name('customers.search')->middleware('permission:quick-sale.view');
});
