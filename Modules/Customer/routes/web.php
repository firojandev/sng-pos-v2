<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;
use Modules\Customer\Http\Controllers\LoyaltyController;

Route::middleware(['auth', 'feature:customers'])->group(function () {
    Route::get('customers/metrics', [CustomerController::class, 'metrics'])
        ->name('customers.metrics')
        ->middleware('permission:customers.view');

    Route::resource('customers', CustomerController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:customers.view')
        ->middlewareFor(['create', 'store'], 'permission:customers.create')
        ->middlewareFor(['edit', 'update'], 'permission:customers.edit')
        ->middlewareFor(['destroy'], 'permission:customers.delete');

    Route::post('customers/{customer}', [CustomerController::class, 'update'])
        ->middleware('permission:customers.edit');
});

Route::middleware(['auth', 'feature:loyalty'])->prefix('loyalty')->name('loyalty.')->group(function () {
    Route::get('/', [LoyaltyController::class, 'index'])->name('index')->middleware('permission:loyalty.view');
    Route::get('customers/{customer}', [LoyaltyController::class, 'customerSummary'])->name('customers.show')->middleware('permission:sales.create');
    Route::put('program', [LoyaltyController::class, 'updateProgram'])->name('program.update')->middleware('permission:loyalty.edit');
    Route::post('members', [LoyaltyController::class, 'enroll'])->name('members.store')->middleware('permission:loyalty.edit');
    Route::delete('members/{membership}', [LoyaltyController::class, 'removeMember'])->name('members.destroy')->middleware('permission:loyalty.edit');
});
