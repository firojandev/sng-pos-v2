<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuditLogController;
use Modules\Core\Http\Controllers\DueLedgerController;
use Modules\Core\Http\Controllers\PageController;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/styleguide', [PageController::class, 'styleguide'])->name('styleguide');
    Route::get('/settings', [PageController::class, 'settings'])->name('settings.index');
    Route::get('/due-ledger', [DueLedgerController::class, 'index'])->name('due-ledger.index');

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
