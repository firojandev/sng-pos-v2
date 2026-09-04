<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuditLogController;
use Modules\Core\Http\Controllers\DueLedgerController;
use Modules\Core\Http\Controllers\PageController;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/styleguide', [PageController::class, 'styleguide'])->name('styleguide');
    Route::get('/settings', [PageController::class, 'settings'])->name('settings.index');
    Route::prefix('due-ledger')->name('due-ledger.')->group(function () {
        Route::get('/', [DueLedgerController::class, 'index'])->name('index');
        Route::get('/sales', [DueLedgerController::class, 'sales'])->name('sales');
        Route::get('/sell', [DueLedgerController::class, 'sales'])->name('sell');
        Route::get('/customer', [DueLedgerController::class, 'sales'])->name('customer');
        Route::get('/purchase', [DueLedgerController::class, 'purchase'])->name('purchase');
        Route::get('/supplier', [DueLedgerController::class, 'purchase'])->name('supplier');
        Route::get('/customers/{customer}/details', [DueLedgerController::class, 'customerDetails'])->name('customer.details');
        Route::get('/suppliers/{supplier}/details', [DueLedgerController::class, 'supplierDetails'])->name('supplier.details');
        Route::get('/customers/{customer}/payment-modal', [DueLedgerController::class, 'customerPaymentModal'])->name('customer.payment-modal');
        Route::post('/customers/{customer}/payment', [DueLedgerController::class, 'storeCustomerPayment'])->name('customer.payment.store');
        Route::get('/suppliers/{supplier}/payment-modal', [DueLedgerController::class, 'supplierPaymentModal'])->name('supplier.payment-modal');
        Route::post('/suppliers/{supplier}/payment', [DueLedgerController::class, 'storeSupplierPayment'])->name('supplier.payment.store');
    });

    Route::middleware(['permission:audit.view', 'feature:audit'])
        ->get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

    Route::middleware(['permission:sales.view', 'feature:sales'])
        ->get('/sales', [PageController::class, 'sales'])->name('sales.index');
    Route::middleware(['permission:purchase.view', 'feature:purchase'])
        ->get('/purchase', [PageController::class, 'purchase'])->name('purchase.index');
    Route::middleware(['permission:stock.view', 'feature:stock'])
        ->get('/stock', [PageController::class, 'stock'])->name('stock.index');
    Route::middleware(['permission:customers.view', 'feature:customers'])
        ->get('/customers', [PageController::class, 'customers'])->name('customers.index');
    Route::middleware(['permission:suppliers.view', 'feature:suppliers'])
        ->get('/suppliers', [PageController::class, 'suppliers'])->name('suppliers.index');
    Route::middleware(['permission:income.view', 'feature:income'])
        ->get('/income', [PageController::class, 'income'])->name('income.index');
    Route::middleware(['permission:expense.view', 'feature:expense'])
        ->get('/expense', [PageController::class, 'expense'])->name('expense.index');
    Route::middleware(['permission:tax.view', 'feature:tax'])
        ->get('/tax', [PageController::class, 'tax'])->name('tax.index');
    Route::middleware(['permission:reports.view', 'feature:reports'])
        ->get('/reports', [PageController::class, 'reports'])->name('reports.index');
});
