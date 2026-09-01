<?php

use Illuminate\Support\Facades\Route;
use Modules\Cashbox\Http\Controllers\CashboxController;

Route::middleware(['auth', 'feature:cashbox'])->prefix('cashbox')->name('cashbox.')->group(function () {
    Route::get('/', [CashboxController::class, 'index'])->name('index')->middleware('permission:cashbox.view');
    Route::post('/cash-in', [CashboxController::class, 'cashIn'])->name('cash-in')->middleware('permission:cashbox.write');
    Route::post('/cash-out', [CashboxController::class, 'cashOut'])->name('cash-out')->middleware('permission:cashbox.write');
});
