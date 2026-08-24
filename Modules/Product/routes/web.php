<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\BatchController;
use Modules\Product\Http\Controllers\BrandController;
use Modules\Product\Http\Controllers\CategoryController;
use Modules\Product\Http\Controllers\ModelController;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\SubCategoryController;
use Modules\Product\Http\Controllers\UnitController;

Route::middleware(['auth', 'permission:products', 'feature:products'])->group(function () {
    Route::resource('products', ProductController::class)->except(['show']);

    Route::prefix('products')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('sub-categories', SubCategoryController::class)->parameters(['sub-categories' => 'subCategory'])->except(['show']);
        Route::resource('units', UnitController::class)->except(['show']);
        Route::resource('brands', BrandController::class)->except(['show']);
        Route::resource('models', ModelController::class)->except(['show']);
        Route::resource('batches', BatchController::class)->except(['show']);
    });
});
