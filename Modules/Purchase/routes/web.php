<?php

use Illuminate\Support\Facades\Route;
use Modules\Purchase\Http\Controllers\PurchaseController;
use Modules\Purchase\Http\Controllers\PurchaseDeliveryOrderController;
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

    // Purchase Delivery Orders
    Route::get('purchase-delivery-orders', [PurchaseDeliveryOrderController::class, 'index'])->name('purchase-delivery-orders.index')->middleware('permission:purchase.view');
    Route::get('purchase-delivery-orders/create', [PurchaseDeliveryOrderController::class, 'create'])->name('purchase-delivery-orders.create')->middleware('permission:purchase.write');
    Route::post('purchase-delivery-orders', [PurchaseDeliveryOrderController::class, 'store'])->name('purchase-delivery-orders.store')->middleware('permission:purchase.write');
    Route::get('purchase-delivery-orders/{deliveryOrder}', [PurchaseDeliveryOrderController::class, 'show'])->name('purchase-delivery-orders.show')->middleware('permission:purchase.view');
    Route::get('purchase-delivery-orders/{deliveryOrder}/edit', [PurchaseDeliveryOrderController::class, 'edit'])->name('purchase-delivery-orders.edit')->middleware('permission:purchase.write');
    Route::put('purchase-delivery-orders/{deliveryOrder}', [PurchaseDeliveryOrderController::class, 'update'])->name('purchase-delivery-orders.update')->middleware('permission:purchase.write');
    Route::delete('purchase-delivery-orders/{deliveryOrder}', [PurchaseDeliveryOrderController::class, 'destroy'])->name('purchase-delivery-orders.destroy')->middleware('permission:purchase.delete');
    Route::post('purchase-delivery-orders/{deliveryOrder}/cancel', [PurchaseDeliveryOrderController::class, 'cancel'])->name('purchase-delivery-orders.cancel')->middleware('permission:purchase.write');
    Route::get('purchase-delivery-orders/{deliveryOrder}/receive', [PurchaseDeliveryOrderController::class, 'receiveForm'])->name('purchase-delivery-orders.receive')->middleware('permission:purchase.write');
    Route::post('purchase-delivery-orders/{deliveryOrder}/receive', [PurchaseDeliveryOrderController::class, 'storeReceive'])->name('purchase-delivery-orders.store-receive')->middleware('permission:purchase.write');
    Route::get('purchase-delivery-orders/{deliveryOrder}/print', [PurchaseDeliveryOrderController::class, 'printOrder'])->name('purchase-delivery-orders.print')->middleware('permission:purchase.view');
    Route::get('purchase-delivery-receipts/{receipt}/print', [PurchaseDeliveryOrderController::class, 'printReceipt'])->name('purchase-delivery-receipts.print')->middleware('permission:purchase.view');
});
