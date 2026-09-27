<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\BatchController;
use Modules\Product\Http\Controllers\BrandController;
use Modules\Product\Http\Controllers\CatalogueController;
use Modules\Product\Http\Controllers\CatalogueMergeController;
use Modules\Product\Http\Controllers\CatalogueReviewController;
use Modules\Product\Http\Controllers\CategoryController;
use Modules\Product\Http\Controllers\ModelController;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\StockController;
use Modules\Product\Http\Controllers\StockTransferController;
use Modules\Product\Http\Controllers\SubCategoryController;
use Modules\Product\Http\Controllers\UnitController;

Route::middleware(['auth', 'feature:products'])->group(function () {
    Route::get('products/{product}/stock-history', [ProductController::class, 'stockHistory'])
        ->name('products.stock-history')
        ->middleware('permission:products.view');

    Route::prefix('products/catalogue')->name('catalogue.')->group(function () {
        Route::get('/', [CatalogueController::class, 'index'])->name('index')->middleware('permission:products.view');
        Route::get('duplicates', [CatalogueController::class, 'duplicates'])->name('duplicates')->middleware('permission:products.create');
        Route::put('categories', [CatalogueController::class, 'updateCategories'])->name('categories.update')->middleware('permission:products.edit');
        Route::post('categories/{category}/list', [CatalogueController::class, 'listCategory'])->name('categories.list')->middleware('permission:products.create');
        Route::post('products/{product}/list', [CatalogueController::class, 'listProduct'])->name('products.list')->middleware('permission:products.create');
    });
    Route::get('products/{product}/shop-price', [CatalogueController::class, 'editShopPrice'])->name('products.shop-price.edit')->middleware('permission:products.edit');
    Route::put('products/{product}/shop-price', [CatalogueController::class, 'updateShopPrice'])->name('products.shop-price.update')->middleware('permission:products.edit');
    Route::post('products/{product}/suggest', [CatalogueController::class, 'suggest'])->name('products.suggest')->middleware('permission:products.edit');

    Route::resource('products', ProductController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:products.view')
        ->middlewareFor(['create', 'store'], 'permission:products.create')
        ->middlewareFor(['edit', 'update'], 'permission:products.edit')
        ->middlewareFor(['destroy'], 'permission:products.delete');

    Route::prefix('products')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show'])
            ->middlewareFor(['index'], 'permission:products.view')
            ->middlewareFor(['create', 'store'], 'permission:products.create')
            ->middlewareFor(['edit', 'update'], 'permission:products.edit')
            ->middlewareFor(['destroy'], 'permission:products.delete');
        Route::resource('sub-categories', SubCategoryController::class)->parameters(['sub-categories' => 'subCategory'])->except(['show'])
            ->middlewareFor(['index'], 'permission:products.view')
            ->middlewareFor(['create', 'store'], 'permission:products.create')
            ->middlewareFor(['edit', 'update'], 'permission:products.edit')
            ->middlewareFor(['destroy'], 'permission:products.delete');
        Route::resource('units', UnitController::class)->except(['show'])
            ->middlewareFor(['index'], 'permission:products.view')
            ->middlewareFor(['create', 'store'], 'permission:products.create')
            ->middlewareFor(['edit', 'update'], 'permission:products.edit')
            ->middlewareFor(['destroy'], 'permission:products.delete');
        Route::resource('brands', BrandController::class)->except(['show'])
            ->middlewareFor(['index'], 'permission:products.view')
            ->middlewareFor(['create', 'store'], 'permission:products.create')
            ->middlewareFor(['edit', 'update'], 'permission:products.edit')
            ->middlewareFor(['destroy'], 'permission:products.delete');
        Route::resource('models', ModelController::class)->except(['show'])
            ->middlewareFor(['index'], 'permission:products.view')
            ->middlewareFor(['create', 'store'], 'permission:products.create')
            ->middlewareFor(['edit', 'update'], 'permission:products.edit')
            ->middlewareFor(['destroy'], 'permission:products.delete');
        Route::resource('batches', BatchController::class)->except(['show'])
            ->middlewareFor(['index'], 'permission:products.view')
            ->middlewareFor(['create', 'store'], 'permission:products.create')
            ->middlewareFor(['edit', 'update'], 'permission:products.edit')
            ->middlewareFor(['destroy'], 'permission:products.delete');
    });
});

Route::middleware(['auth', 'feature:stock'])->group(function () {
    Route::get('stock', [StockController::class, 'index'])->name('stock.index')->middleware('permission:stock.view');
    Route::post('stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust')->middleware('permission:stock.adjust');
    Route::get('stock/history', [StockController::class, 'history'])->name('stock.history')->middleware('permission:stock.view');

    Route::get('stock-transfers', [StockTransferController::class, 'index'])->name('stock-transfers.index')->middleware('permission:stock.view');
    Route::get('stock-transfers/create', [StockTransferController::class, 'create'])->name('stock-transfers.create')->middleware('permission:stock.create');
    Route::post('stock-transfers', [StockTransferController::class, 'store'])->name('stock-transfers.store')->middleware('permission:stock.create');
    Route::get('stock-transfers/{transfer}', [StockTransferController::class, 'show'])->name('stock-transfers.show')->middleware('permission:stock.view');
    Route::post('stock-transfers/{transfer}/approve', [StockTransferController::class, 'approve'])->name('stock-transfers.approve')->middleware('permission:stock.transfer');
    Route::post('stock-transfers/{transfer}/dispatch', [StockTransferController::class, 'dispatch'])->name('stock-transfers.dispatch')->middleware('permission:stock.transfer');
    Route::post('stock-transfers/{transfer}/receive', [StockTransferController::class, 'receive'])->name('stock-transfers.receive')->middleware('permission:stock.transfer');
    Route::post('stock-transfers/{transfer}/cancel', [StockTransferController::class, 'cancel'])->name('stock-transfers.cancel')->middleware('permission:stock.delete');
});

Route::middleware(['auth', 'role:Super Admin'])->prefix('catalogue-review')->name('catalogue-review.')->group(function () {
    Route::get('/', [CatalogueReviewController::class, 'index'])->name('index');
    Route::post('{product}/approve', [CatalogueReviewController::class, 'approve'])->name('approve');
    Route::post('{product}/reject', [CatalogueReviewController::class, 'reject'])->name('reject');
});

Route::middleware(['auth', 'role:Super Admin'])->prefix('catalogue-merge')->name('catalogue-merge.')->group(function () {
    Route::get('/', [CatalogueMergeController::class, 'index'])->name('index');
    Route::post('/', [CatalogueMergeController::class, 'merge'])->name('merge');
});
