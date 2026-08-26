<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\DueLedgerController;
use Modules\Core\Http\Controllers\PageController;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/settings', [PageController::class, 'settings'])->name('settings.index');
    Route::get('/due-ledger', [DueLedgerController::class, 'index'])->name('due-ledger.index');

    Route::middleware(['permission:sales', 'feature:sales'])
        ->get('/sales', [PageController::class, 'sales'])->name('sales.index');
    Route::middleware(['permission:purchase', 'feature:purchase'])
        ->get('/purchase', [PageController::class, 'purchase'])->name('purchase.index');
    Route::middleware(['permission:stock', 'feature:stock'])
        ->get('/stock', [PageController::class, 'stock'])->name('stock.index');
    Route::middleware(['permission:customers', 'feature:customers'])
        ->get('/customers', [PageController::class, 'customers'])->name('customers.index');
    Route::middleware(['permission:suppliers', 'feature:suppliers'])
        ->get('/suppliers', [PageController::class, 'suppliers'])->name('suppliers.index');
    Route::middleware(['permission:income', 'feature:income'])
        ->get('/income', [PageController::class, 'income'])->name('income.index');
    Route::middleware(['permission:expense', 'feature:expense'])
        ->get('/expense', [PageController::class, 'expense'])->name('expense.index');
    Route::middleware(['permission:tax', 'feature:tax'])
        ->get('/tax', [PageController::class, 'tax'])->name('tax.index');
    Route::middleware(['permission:reports', 'feature:reports'])
        ->get('/reports', [PageController::class, 'reports'])->name('reports.index');
});
