<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\Http\Controllers\ReportController;

Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');

    Route::middleware(['permission:report-sales.view', 'feature:report-sales'])
        ->get('/sales', [ReportController::class, 'sales'])->name('sales');

    Route::middleware(['permission:report-purchase.view', 'feature:report-purchase'])
        ->get('/purchase', [ReportController::class, 'purchase'])->name('purchase');

    Route::middleware(['permission:report-stock.view', 'feature:report-stock'])
        ->get('/stock', [ReportController::class, 'stock'])->name('stock');

    Route::middleware(['permission:report-products.view', 'feature:report-products'])
        ->get('/products', [ReportController::class, 'products'])->name('products');

    Route::middleware(['permission:report-profit-loss.view', 'feature:report-profit-loss'])
        ->get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');

    Route::middleware(['permission:report-income.view', 'feature:report-income'])
        ->get('/income', [ReportController::class, 'income'])->name('income');

    Route::middleware(['permission:report-expense.view', 'feature:report-expense'])
        ->get('/expense', [ReportController::class, 'expense'])->name('expense');
});
