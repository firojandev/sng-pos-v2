<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\BatchController;
use Modules\Product\Http\Controllers\BrandController;
use Modules\Product\Http\Controllers\CategoryController;
use Modules\Product\Http\Controllers\ModelController;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\StockController;
use Modules\Product\Http\Controllers\StockTransferController;
use Modules\Product\Http\Controllers\SubCategoryController;
use Modules\Product\Http\Controllers\UnitController;

Route::middleware(['auth', 'feature:products'])->group(function () {
    Route::resource('products', ProductController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:products.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:products.write')
        ->middlewareFor(['destroy'], 'permission:products.delete');

    Route::prefix('products')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show'])
            ->middlewareFor(['index'], 'permission:products.view')
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:products.write')
            ->middlewareFor(['destroy'], 'permission:products.delete');
        Route::resource('sub-categories', SubCategoryController::class)->parameters(['sub-categories' => 'subCategory'])->except(['show'])
            ->middlewareFor(['index'], 'permission:products.view')
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:products.write')
            ->middlewareFor(['destroy'], 'permission:products.delete');
        Route::resource('units', UnitController::class)->except(['show'])
            ->middlewareFor(['index'], 'permission:products.view')
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:products.write')
            ->middlewareFor(['destroy'], 'permission:products.delete');
        Route::resource('brands', BrandController::class)->except(['show'])
            ->middlewareFor(['index'], 'permission:products.view')
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:products.write')
            ->middlewareFor(['destroy'], 'permission:products.delete');
        Route::resource('models', ModelController::class)->except(['show'])
            ->middlewareFor(['index'], 'permission:products.view')
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:products.write')
            ->middlewareFor(['destroy'], 'permission:products.delete');
        Route::resource('batches', BatchController::class)->except(['show'])
            ->middlewareFor(['index'], 'permission:products.view')
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:products.write')
            ->middlewareFor(['destroy'], 'permission:products.delete');
    });
});

Route::middleware(['auth', 'feature:stock'])->group(function () {
    Route::get('stock', [StockController::class, 'index'])->name('stock.index')->middleware('permission:stock.view');
    Route::post('stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust')->middleware('permission:stock.write');
    Route::get('stock/history', [StockController::class, 'history'])->name('stock.history')->middleware('permission:stock.view');

    Route::get('stock-transfers', [StockTransferController::class, 'index'])->name('stock-transfers.index')->middleware('permission:stock.view');
    Route::get('stock-transfers/create', [StockTransferController::class, 'create'])->name('stock-transfers.create')->middleware('permission:stock.write');
    Route::post('stock-transfers', [StockTransferController::class, 'store'])->name('stock-transfers.store')->middleware('permission:stock.write');
    Route::post('stock-transfers/{transfer}/approve', [StockTransferController::class, 'approve'])->name('stock-transfers.approve')->middleware('permission:stock.write');
    Route::post('stock-transfers/{transfer}/dispatch', [StockTransferController::class, 'dispatch'])->name('stock-transfers.dispatch')->middleware('permission:stock.write');
    Route::post('stock-transfers/{transfer}/receive', [StockTransferController::class, 'receive'])->name('stock-transfers.receive')->middleware('permission:stock.write');
    Route::post('stock-transfers/{transfer}/cancel', [StockTransferController::class, 'cancel'])->name('stock-transfers.cancel')->middleware('permission:stock.delete');
});
