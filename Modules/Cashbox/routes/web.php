<?php

use Illuminate\Support\Facades\Route;
use Modules\Cashbox\Http\Controllers\CashboxController;

Route::middleware(['auth', 'permission:cashbox', 'feature:cashbox'])->prefix('cashbox')->name('cashbox.')->group(function () {
    Route::get('/', [CashboxController::class, 'index'])->name('index');
    Route::post('/cash-in', [CashboxController::class, 'cashIn'])->name('cash-in');
    Route::post('/cash-out', [CashboxController::class, 'cashOut'])->name('cash-out');
});
