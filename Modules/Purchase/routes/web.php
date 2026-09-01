<?php

use Illuminate\Support\Facades\Route;
use Modules\Purchase\Http\Controllers\PurchaseController;
use Modules\Purchase\Http\Controllers\PurchaseReturnController;

Route::middleware(['auth', 'feature:purchase'])->group(function () {
    Route::get('purchase/ledger', [PurchaseController::class, 'ledger'])->name('purchase.ledger')->middleware('permission:purchase.view');
    Route::get('purchase-returns', [PurchaseReturnController::class, 'index'])->name('purchase-returns.index')->middleware('permission:purchase.view');
    Route::get('purchase/{purchase}/returns/create', [PurchaseReturnController::class, 'create'])->name('purchase-returns.create')->middleware('permission:purchase.write');
    Route::post('purchase/{purchase}/returns', [PurchaseReturnController::class, 'store'])->name('purchase-returns.store')->middleware('permission:purchase.write');
    Route::resource('purchase', PurchaseController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:purchase.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:purchase.write')
        ->middlewareFor(['destroy'], 'permission:purchase.delete');
});
